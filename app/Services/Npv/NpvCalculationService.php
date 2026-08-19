<?php

namespace App\Services\Npv;

use App\Models\Agreement;
use App\Models\FinanceSetting;
use App\Services\Npv\Dto\NpvCalculationInput;
use App\Services\Npv\Dto\NpvCalculationResult;

class NpvCalculationService
{
    public function __construct(
        private MonthlyScheduleGenerator $scheduleGenerator,
        private DiscountingService $discountingService,
    ) {}

    /**
     * Run full NPV / Present Value calculation for an agreement ID.
     *
     * @param NpvCalculationInput $input
     * @return NpvCalculationResult
     */
    public function calculate(NpvCalculationInput $input): NpvCalculationResult
    {
        $agreement = Agreement::with([
            'vendor',
            'floors.building',
            'rentBases' => fn($q) => $q->orderBy('id', 'desc')->with('components'),
            'rentIncrements',
            'securityDeposits' => fn($q) => $q->orderBy('id', 'desc'),
        ])->findOrFail($input->agreementId);

        return $this->calculateForAgreement($agreement, $input);
    }

    /**
     * Run full NPV calculation for an already loaded Agreement model instance.
     * Useful for batch processing without extra DB queries.
     *
     * @param Agreement $agreement
     * @param NpvCalculationInput $input
     * @return NpvCalculationResult
     */
    public function calculateForAgreement(Agreement $agreement, NpvCalculationInput $input): NpvCalculationResult
    {
        // Source annual discount rate: user override > DB setting > 12.16 default
        $annualRate = $input->annualDiscountRate > 0
            ? $input->annualDiscountRate
            : FinanceSetting::getValue('npv_annual_discount_rate', 12.16);

        $monthlyRate = $this->discountingService->annualToMonthlyRate($annualRate);

        // Generate schedule
        $cashFlows = $this->scheduleGenerator->generateSchedule(
            agreement: $agreement,
            baseDate: $input->baseDate,
            annualDiscountRate: $annualRate,
            customTaxRate: $input->customTaxRate,
            customVatRate: $input->customVatRate
        );

        // Aggregate summary metrics
        $totalMonths = count(array_filter($cashFlows, fn($cf) => $cf->periodIndex > 0));
        $totalNPV = 0.0;
        $totalUndiscountedOutflow = 0.0;
        $totalGrossRent = 0.0;
        $totalAdvanceDeductions = 0.0;
        $totalDepositRefunds = 0.0;

        foreach ($cashFlows as $cf) {
            $totalNPV += $cf->presentValue;
            $totalUndiscountedOutflow += $cf->netOutflow;
            $totalGrossRent += $cf->totalGrossRent;
            $totalAdvanceDeductions += $cf->advanceDeduction;
            $totalDepositRefunds += $cf->depositRefund;
        }

        // Fetch security deposit summary totals from DB/relation
        $firstDeposit = $agreement->securityDeposits->first();
        $absorbableTotal = (float) ($firstDeposit->security_deposit_absorbable ?? 0);
        $nonAbsorbableTotal = (float) ($firstDeposit->security_deposit_non_absorbable ?? 0);

        return new NpvCalculationResult(
            agreement: $agreement,
            baseDate: $input->baseDate,
            expiryDate: $agreement->to_date ?? '',
            totalMonths: $totalMonths,
            annualDiscountRate: $annualRate,
            monthlyDiscountRate: $monthlyRate,
            totalNPV: round($totalNPV, 2),
            totalUndiscountedOutflow: round($totalUndiscountedOutflow, 2),
            totalGrossRent: round($totalGrossRent, 2),
            totalAdvanceDeductions: round($totalAdvanceDeductions, 2),
            totalDepositRefunds: round($totalDepositRefunds, 2),
            absorbableAdvanceTotal: round($absorbableTotal, 2),
            nonAbsorbableDepositTotal: round($nonAbsorbableTotal, 2),
            cashFlows: $cashFlows
        );
    }
}
