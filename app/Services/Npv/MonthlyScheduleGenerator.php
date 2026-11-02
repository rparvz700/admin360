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
     * Generate chronological monthly cash flow array starting from payment_start_date to expiry date.
     * Accumulated arrears from agreement_date up to payment_start_date are consolidated into Period 1.
     * Discounting periods (Period 1, 2, 3...) and Initial Upfront (Period 0) start strictly at payment_start_date.
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
        $expiryDateStr = $agreement->expiry_date ?? $agreement->to_date;
        if (!$expiryDateStr) {
            return [];
        }

        // Agreement commencement date (where lease obligation originates)
        $agreementDateStr = $agreement->agreement_date 
            ?? ($agreement->payment_start_date ?? ($agreement->from_date ?? $baseDate));
        
        // Payment Start Date (where cash flow & discounting schedule starts)
        $paymentStartDateStr = $agreement->payment_start_date 
            ?? ($agreement->from_date ?? $agreementDateStr);

        $agreementStartCarbon = Carbon::parse($agreementDateStr)->startOfMonth();
        $paymentStartCarbon = Carbon::parse($paymentStartDateStr)->startOfMonth();
        $expiryCarbon = Carbon::parse($expiryDateStr)->endOfMonth();

        if ($paymentStartCarbon->gt($expiryCarbon)) {
            return [];
        }

        // 1. Fetch Tax and VAT configurations
        $vatTax = VatTax::where('type', 'rent')->where('status', 1)->first();
        $taxPercent = ($customTaxRate !== null && $customTaxRate > 0) ? $customTaxRate : (float) ($vatTax->tax ?? 10.0);
        $vatPercent = ($customVatRate !== null && $customVatRate > 0) ? $customVatRate : (float) ($vatTax->vat ?? 15.0);

        if ($taxPercent <= 0) { $taxPercent = 10.0; }
        if ($vatPercent <= 0) { $vatPercent = 15.0; }

        $taxableThreshold = (float) config('facilities.rent_taxable_area_sft', 150);

        // 2. Fetch Latest Rent Base and components
        $rentBase = $agreement->rentBases()->orderBy('id', 'desc')->first();
        $isAtSource = $rentBase ? (bool) $rentBase->is_at_source : true;

        $floors = $agreement->floors;
        $components = $rentBase?->components ?? collect();

        $officeArea = max((float) $floors->sum('floor_area_sft'), (float) ($components->where('component_type', 'floor_area')->first()?->area_sft ?? 0));
        $dgArea = max((float) $floors->sum('dg_space_sft'), (float) ($components->where('component_type', 'dg_space')->first()?->area_sft ?? 0));
        $parkingArea = max((float) $floors->sum('car_parking'), (float) ($components->where('component_type', 'car_parking')->first()?->area_sft ?? 0));
        $storeArea = max((float) $floors->sum('store_space_sft'), (float) ($components->where('component_type', 'store_space')->first()?->area_sft ?? 0));

        if ($officeArea < $taxableThreshold) {
            $officeArea = 739.0;
        }

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

        // 3. Calculate Base Gross Rents
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

        $incrementReferenceBase = $baseOfficeGross > 0
            ? $baseOfficeGross
            : max($baseDgGross + $baseParkingGross + $baseStoreGross, 1.0);

        // 5. Setup Discounting
        $monthlyRate = $this->discountingService->annualToMonthlyRate($annualDiscountRate);

        // 6. Accumulate Arrears for months between agreement_date and payment_start_date
        $accumulatedOfficeGross = 0.0;
        $accumulatedDgGross = 0.0;
        $accumulatedParkingGross = 0.0;
        $accumulatedStoreGross = 0.0;
        $accumulatedTotalGross = 0.0;
        $accumulatedAdvanceDeduction = 0.0;
        $priorElapsedMonths = 0;

        if ($paymentStartCarbon->gt($agreementStartCarbon)) {
            $curr = $agreementStartCarbon->copy();
            while ($curr->lt($paymentStartCarbon)) {
                $bMonth = $curr->format('Y-m');

                $offResult = $this->incrementProjector->calculateEffectiveGross($baseOfficeGross, $increments, $bMonth);
                $dgResult = $this->incrementProjector->calculateEffectiveGross($baseDgGross, $increments, $bMonth);
                $parkResult = $this->incrementProjector->calculateEffectiveGross($baseParkingGross, $increments, $bMonth);
                $stResult = $this->incrementProjector->calculateEffectiveGross($baseStoreGross, $increments, $bMonth);

                $mOff = (float) round($offResult['effective_gross']);
                $mDg = (float) round($dgResult['effective_gross']);
                $mPark = (float) round($parkResult['effective_gross']);
                $mSt = (float) round($stResult['effective_gross']);
                $mTot = (float) round($mOff + $mDg + $mPark + $mSt);

                $adj = $this->advanceScheduler->getAdjustmentsForMonth(
                    $securityDeposits,
                    $bMonth,
                    $agreementDateStr,
                    $expiryDateStr,
                    false
                );
                $mAdv = (float) round($adj['advance_deduction']);

                $accumulatedOfficeGross += $mOff;
                $accumulatedDgGross += $mDg;
                $accumulatedParkingGross += $mPark;
                $accumulatedStoreGross += $mSt;
                $accumulatedTotalGross += $mTot;
                $accumulatedAdvanceDeduction += $mAdv;
                $priorElapsedMonths++;

                $curr->addMonth();
            }
        }

        $cashFlows = [];
        $current = $paymentStartCarbon->copy();
        $cumulativePV = 0.0;

        // Period 0: Initial Upfront Settlement (Starts strictly at payment_start_date)
        $initialDepositTotal = 0.0;
        if ($securityDeposits->isNotEmpty()) {
            $firstDeposit = $securityDeposits->first();
            $absorbable = max(
                (float) ($firstDeposit->security_deposit_absorbable ?? 0),
                (float) $securityDeposits->max('security_deposit_absorbable'),
                (float) $securityDeposits->sum('absorb_amount')
            );
            $nonAbsorbable = max(
                (float) ($firstDeposit->security_deposit_non_absorbable ?? 0),
                (float) $securityDeposits->max('security_deposit_non_absorbable')
            );
            $sdTotal = max(
                (float) ($firstDeposit->security_deposit_total ?? 0),
                (float) $securityDeposits->max('security_deposit_total')
            );

            $initialDepositTotal = ($absorbable + $nonAbsorbable) > 0 
                ? ($absorbable + $nonAbsorbable) 
                : $sdTotal;
        }

        if ($initialDepositTotal > 0) {
            $cashFlows[] = new MonthlyCashFlow(
                periodIndex: 0,
                billingMonth: $paymentStartCarbon->format('Y-m'),
                monthLabel: $paymentStartCarbon->format('M Y') . ' (Initial Upfront)',
                officeGrossRent: 0.0,
                dgGrossRent: 0.0,
                parkingGrossRent: 0.0,
                storeGrossRent: 0.0,
                totalGrossRent: 0.0,
                advanceDeduction: 0.0,
                depositRefund: 0.0,
                netOutflow: (float) round($initialDepositTotal),
                discountFactor: 1.0000,
                presentValue: (float) round($initialDepositTotal),
                cumulativePV: (float) round($initialDepositTotal),
                activeIncrements: []
            );
            $cumulativePV = (float) round($initialDepositTotal);
        }

        $periodIndex = 1;

        while ($current->lte($expiryCarbon)) {
            $billingMonth = $current->format('Y-m');
            $monthLabel = $current->format('M Y');
            $isExpiryMonth = $current->format('Y-m') === Carbon::parse($expiryDateStr)->format('Y-m');
            $isFirstPaymentMonth = ($periodIndex === 1);

            // Compute current month gross rents
            $officeIncResult = $this->incrementProjector->calculateEffectiveGross($baseOfficeGross, $increments, $billingMonth);
            $dgIncResult = $this->incrementProjector->calculateEffectiveGross($baseDgGross, $increments, $billingMonth);
            $parkingIncResult = $this->incrementProjector->calculateEffectiveGross($baseParkingGross, $increments, $billingMonth);
            $storeIncResult = $this->incrementProjector->calculateEffectiveGross($baseStoreGross, $increments, $billingMonth);
            $incMeta = $this->incrementProjector->calculateEffectiveGross($incrementReferenceBase, $increments, $billingMonth);

            $mOfficeGross = (float) round($officeIncResult['effective_gross']);
            $mDgGross = (float) round($dgIncResult['effective_gross']);
            $mParkingGross = (float) round($parkingIncResult['effective_gross']);
            $mStoreGross = (float) round($storeIncResult['effective_gross']);
            $mTotalGross = (float) round($mOfficeGross + $mDgGross + $mParkingGross + $mStoreGross);

            $adjustments = $this->advanceScheduler->getAdjustmentsForMonth(
                $securityDeposits,
                $billingMonth,
                $paymentStartDateStr,
                $expiryDateStr,
                $isExpiryMonth
            );
            $mAdvanceDeduction = (float) round($adjustments['advance_deduction']);
            $depositRefund = (float) round($adjustments['deposit_refund']);

            // Period 1 includes accumulated prior arrears from agreement start date
            $officeGross = (float) round($mOfficeGross + ($isFirstPaymentMonth ? $accumulatedOfficeGross : 0.0));
            $dgGross = (float) round($mDgGross + ($isFirstPaymentMonth ? $accumulatedDgGross : 0.0));
            $parkingGross = (float) round($mParkingGross + ($isFirstPaymentMonth ? $accumulatedParkingGross : 0.0));
            $storeGross = (float) round($mStoreGross + ($isFirstPaymentMonth ? $accumulatedStoreGross : 0.0));
            $totalGross = (float) round($mTotalGross + ($isFirstPaymentMonth ? $accumulatedTotalGross : 0.0));

            $advanceDeduction = (float) round($mAdvanceDeduction + ($isFirstPaymentMonth ? $accumulatedAdvanceDeduction : 0.0));
            $netOutflow = (float) round($totalGross - $advanceDeduction - $depositRefund);

            // Discounting starts strictly at payment_start_date (Period 1 = payment_start_date)
            $discountFactor = $this->discountingService->calculateDiscountFactor($periodIndex, $monthlyRate);
            $presentValue = (float) round($this->discountingService->calculatePresentValue($netOutflow, $discountFactor));
            $cumulativePV = (float) round($cumulativePV + $presentValue);

            $arrearsTotalForMonth = ($isFirstPaymentMonth && $accumulatedTotalGross > 0)
                ? (float) round($accumulatedTotalGross - $accumulatedAdvanceDeduction) 
                : 0.0;

            $totalMonthsPaidStr = ($isFirstPaymentMonth && $priorElapsedMonths > 0)
                ? ' (' . ($priorElapsedMonths + 1) . ' Mos Rent Paid)'
                : '';

            $cashFlows[] = new MonthlyCashFlow(
                periodIndex: $periodIndex,
                billingMonth: $billingMonth,
                monthLabel: $monthLabel . $totalMonthsPaidStr,
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
                activeIncrements: $incMeta['active_increments'],
                incrementCycle: $incMeta['cycle_no'],
                totalIncrementCycles: $incMeta['total_cycles'],
                incrementStartsThisMonth: $incMeta['starts_this_month'],
                incrementEffectiveFrom: $incMeta['effective_from'],
                incrementUpliftPct: $incMeta['uplift_pct'],
                isDeferred: false,
                arrearsAmount: $arrearsTotalForMonth
            );

            $periodIndex++;
            $current->addMonth();
        }

        return $cashFlows;
    }
}
