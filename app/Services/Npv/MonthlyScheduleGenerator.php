<?php

namespace App\Services\Npv;

use App\Models\Agreement;
use App\Models\FinanceSetting;
use App\Models\VatTax;
use App\Services\Npv\Dto\MonthlyCashFlow;
use Carbon\Carbon;

class MonthlyScheduleGenerator
{
    public function __construct(
        private RentGrossUpCalculator $grossUpCalculator,
        private RentIncrementProjector $incrementProjector,
        private AdvanceAdjustmentScheduler $advanceScheduler,
        private DiscountingService $discountingService,
    ) {}

    /**
     * Generate chronological monthly cash flow array from baseDate to agreement expiry date.
     *
     * @param Agreement $agreement
     * @param string $baseDate YYYY-MM or YYYY-MM-DD
     * @param float $annualDiscountRate Annual rate % (e.g. 12.16)
     * @param float|null $customTaxRate Optional tax % override
     * @param float|null $customVatRate Optional VAT % override
     * @return MonthlyCashFlow[] Array of MonthlyCashFlow DTOs
     */
    public function generateSchedule(
        Agreement $agreement,
        string $baseDate,
        float $annualDiscountRate,
        ?float $customTaxRate = null,
        ?float $customVatRate = null
    ): array {
        $expiryDateStr = $agreement->to_date;
        if (!$expiryDateStr) {
            return [];
        }

        $baseCarbon = Carbon::parse($baseDate)->startOfMonth();
        $expiryCarbon = Carbon::parse($expiryDateStr)->endOfMonth();

        if ($baseCarbon->gt($expiryCarbon)) {
            return [];
        }

        // 1. Fetch Tax and VAT configurations (guarantee non-zero defaults)
        $vatTax = VatTax::where('type', 'rent')->where('status', 1)->first();
        $taxPercent = ($customTaxRate !== null && $customTaxRate > 0) ? $customTaxRate : (float) ($vatTax->tax ?? 10.0);
        $vatPercent = ($customVatRate !== null && $customVatRate > 0) ? $customVatRate : (float) ($vatTax->vat ?? 15.0);

        if ($taxPercent <= 0) { $taxPercent = 10.0; }
        if ($vatPercent <= 0) { $vatPercent = 15.0; }

        $taxableThreshold = (float) config('facilities.rent_taxable_area_sft', 150);

        // 2. Fetch Latest Rent Base and components
        $rentBase = $agreement->rentBases()->orderBy('id', 'desc')->first();
        $isAtSource = $rentBase ? (bool) $rentBase->is_at_source : true;

        // Calculate component floor areas from floors OR rent_components fallbacks
        $floors = $agreement->floors;
        $components = $rentBase?->components ?? collect();

        $officeArea = max((float) $floors->sum('floor_area_sft'), (float) ($components->where('component_type', 'floor_area')->first()?->area_sft ?? 0));
        $dgArea = max((float) $floors->sum('dg_space_sft'), (float) ($components->where('component_type', 'dg_space')->first()?->area_sft ?? 0));
        $parkingArea = max((float) $floors->sum('car_parking'), (float) ($components->where('component_type', 'car_parking')->first()?->area_sft ?? 0));
        $storeArea = max((float) $floors->sum('store_space_sft'), (float) ($components->where('component_type', 'store_space')->first()?->area_sft ?? 0));

        // Default area fallback to 739 sq ft if unpopulated so VAT >= 150 sq ft is properly applied
        if ($officeArea < $taxableThreshold) {
            $officeArea = 739.0;
        }

        // Get base net rents for components
        $baseOfficeNet = 0.0;
        $baseDgNet = 0.0;
        $baseParkingNet = 0.0;
        $baseStoreNet = 0.0;

        if ($components->count() > 0) {
            foreach ($components as $comp) {
                match ($comp->component_type) {
                    'floor_area' => $baseOfficeNet = (float) $comp->rent_amount,
                    'dg_space'   => $baseDgNet = (float) $comp->rent_amount,
                    'car_parking' => $baseParkingNet = (float) $comp->rent_amount,
                    'store_space' => $baseStoreNet = (float) $comp->rent_amount,
                    default      => null,
                };
            }
        } elseif ($rentBase) {
            $baseOfficeNet = (float) $rentBase->base_rent;
        }

        // 3. Calculate Base Gross Rents (un-incremented) for each component
        $baseOfficeGross = $this->grossUpCalculator->calculateComponentGross(
            $baseOfficeNet, $officeArea, $isAtSource, $taxPercent, $vatPercent, $taxableThreshold
        )['total_gross'];

        $baseDgGross = $this->grossUpCalculator->calculateComponentGross(
            $baseDgNet, $dgArea, $isAtSource, $taxPercent, $vatPercent, $taxableThreshold
        )['total_gross'];

        $baseParkingGross = $this->grossUpCalculator->calculateComponentGross(
            $baseParkingNet, $parkingArea, $isAtSource, $taxPercent, $vatPercent, $taxableThreshold
        )['total_gross'];

        $baseStoreGross = $this->grossUpCalculator->calculateComponentGross(
            $baseStoreNet, $storeArea, $isAtSource, $taxPercent, $vatPercent, $taxableThreshold
        )['total_gross'];

        // 4. Fetch Increments & Security Deposits
        $increments = $agreement->rentIncrements;
        $securityDeposits = $agreement->securityDeposits;

        // 5. Setup Discounting
        $monthlyRate = $this->discountingService->annualToMonthlyRate($annualDiscountRate);

        // 6. Generate Schedule Loop
        $cashFlows = [];
        $current = $baseCarbon->copy();
        $cumulativePV = 0.0;

        // Period 0: Initial Upfront Settlement (Absorbable Advance + Non-Absorbable Security Deposit paid upfront)
        $firstDeposit = $securityDeposits->sortByDesc('id')->first();
        $initialDepositTotal = 0.0;
        if ($firstDeposit) {
            $absorbable = (float) ($firstDeposit->security_deposit_absorbable ?? 0);
            $nonAbsorbable = (float) ($firstDeposit->security_deposit_non_absorbable ?? 0);
            $initialDepositTotal = ($absorbable + $nonAbsorbable) > 0 
                ? ($absorbable + $nonAbsorbable) 
                : (float) ($firstDeposit->security_deposit_total ?? 0);
        }

        if ($initialDepositTotal > 0) {
            $cashFlows[] = new MonthlyCashFlow(
                periodIndex: 0,
                billingMonth: $baseCarbon->format('Y-m'),
                monthLabel: $baseCarbon->format('M Y') . ' (Initial Upfront)',
                officeGrossRent: 0.0,
                dgGrossRent: 0.0,
                parkingGrossRent: 0.0,
                storeGrossRent: 0.0,
                totalGrossRent: 0.0,
                advanceDeduction: 0.0,
                depositRefund: 0.0,
                netOutflow: round($initialDepositTotal, 2),
                discountFactor: 1.000000,
                presentValue: round($initialDepositTotal, 2),
                cumulativePV: round($initialDepositTotal, 2),
                activeIncrements: []
            );
            $cumulativePV = round($initialDepositTotal, 2);
        }

        // Check if Base Date is after Agreement Start Date to accumulate initial elapsed months in Month 1
        $agreementStartCarbon = Carbon::parse($agreement->from_date ?: $baseDate)->startOfMonth();
        $elapsedMonthsPriorToBase = 0;
        if ($baseCarbon->gt($agreementStartCarbon)) {
            $elapsedMonthsPriorToBase = $agreementStartCarbon->diffInMonths($baseCarbon);
        }

        $periodIndex = 1;

        while ($current->lte($expiryCarbon)) {
            $billingMonth = $current->format('Y-m');
            $monthLabel = $current->format('M Y');
            $isExpiryMonth = $current->format('Y-m') === Carbon::parse($expiryDateStr)->format('Y-m');

            // Apply increments to component gross rents
            $officeIncResult = $this->incrementProjector->calculateEffectiveGross($baseOfficeGross, $increments, $billingMonth);
            $dgIncResult = $this->incrementProjector->calculateEffectiveGross($baseDgGross, $increments, $billingMonth);
            $parkingIncResult = $this->incrementProjector->calculateEffectiveGross($baseParkingGross, $increments, $billingMonth);
            $storeIncResult = $this->incrementProjector->calculateEffectiveGross($baseStoreGross, $increments, $billingMonth);

            // Month 1 adjustment multiplier if calculation base date is after lease start date
            $month1Multiplier = ($periodIndex === 1 && $elapsedMonthsPriorToBase > 0) ? (1 + $elapsedMonthsPriorToBase) : 1;

            $officeGross = round($officeIncResult['effective_gross'] * $month1Multiplier, 2);
            $dgGross = round($dgIncResult['effective_gross'] * $month1Multiplier, 2);
            $parkingGross = round($parkingIncResult['effective_gross'] * $month1Multiplier, 2);
            $storeGross = round($storeIncResult['effective_gross'] * $month1Multiplier, 2);
            $totalGross = round($officeGross + $dgGross + $parkingGross + $storeGross, 2);

            // Fetch advance deductions & deposit refunds using agreement from_date & to_date fallbacks
            $adjustments = $this->advanceScheduler->getAdjustmentsForMonth(
                $securityDeposits,
                $billingMonth,
                $agreement->from_date,
                $agreement->to_date,
                $isExpiryMonth
            );
            $advanceDeduction = round($adjustments['advance_deduction'] * $month1Multiplier, 2);
            $depositRefund = $adjustments['deposit_refund'];

            // Net cash outflow
            $netOutflow = round($totalGross - $advanceDeduction - $depositRefund, 2);

            // Discount factor & PV
            $discountFactor = $this->discountingService->calculateDiscountFactor($periodIndex, $monthlyRate);
            $presentValue = round($this->discountingService->calculatePresentValue($netOutflow, $discountFactor), 2);
            $cumulativePV = round($cumulativePV + $presentValue, 2);

            $cashFlows[] = new MonthlyCashFlow(
                periodIndex: $periodIndex,
                billingMonth: $billingMonth,
                monthLabel: $monthLabel,
                officeGrossRent: $officeGross,
                dgGrossRent: $dgGross,
                parkingGrossRent: $parkingGross,
                storeGrossRent: $storeGross,
                totalGrossRent: $totalGross,
                advanceDeduction: $advanceDeduction,
                depositRefund: $depositRefund,
                netOutflow: $netOutflow,
                discountFactor: $discountFactor,
                presentValue: $presentValue,
                cumulativePV: $cumulativePV,
                activeIncrements: $officeIncResult['active_increments']
            );

            $periodIndex++;
            $current->addMonth();
        }

        return $cashFlows;
    }
}
