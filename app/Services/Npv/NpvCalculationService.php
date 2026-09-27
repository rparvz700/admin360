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
    public function calculateForAgreement(Agreement $agreement, NpvCalculationInput $input, bool $allowZeroRate = false): NpvCalculationResult
    {
        // Source annual discount rate: user override > agreement configured rate > fallback setting
        if ($input->annualDiscountRate > 0) {
            $annualRate = $input->annualDiscountRate;
        } elseif (!is_null($agreement->npv_interest_rate) && (float) $agreement->npv_interest_rate > 0) {
            $annualRate = (float) $agreement->npv_interest_rate;
        } else {
            $annualRate = $allowZeroRate ? 0.0 : (float) FinanceSetting::getValue('npv_annual_discount_rate', 12.16);
        }

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

        $totalNPV = round($totalNPV, 2);

        // Fetch security deposit summary totals from DB/relation
        $deposits = $agreement->securityDeposits;
        $firstDeposit = $deposits->first();
        $absorbableTotal = max(
            (float) ($firstDeposit->security_deposit_absorbable ?? 0),
            (float) $deposits->max('security_deposit_absorbable'),
            (float) $deposits->sum('absorb_amount')
        );
        $nonAbsorbableTotal = max(
            (float) ($firstDeposit->security_deposit_non_absorbable ?? 0),
            (float) $deposits->max('security_deposit_non_absorbable')
        );

        // IFRS 16 Lease Obligation Amortization & PPE Movement Schedule
        // Applicable when Total NPV is >= 1,000,000 BDT
        $hasIfrs16Schedule = ($totalNPV >= 1000000.0);
        $initialLeaseLiability = null;
        $initialRouAsset = null;
        $totalFinanceExpense = null;
        $totalLeasePayments = null;
        $totalDepreciation = null;
        $monthlyDepreciation = null;

        if ($hasIfrs16Schedule && $totalMonths > 0) {
            // Period 0 Net Outflow (Initial Upfront deposit/advance)
            $period0Cf = collect($cashFlows)->firstWhere('periodIndex', 0);
            $period0Outflow = $period0Cf ? (float) $period0Cf->netOutflow : 0.0;

            // Initial Lease Obligation = Total NPV - Period 0 Outflow
            $initialLeaseLiability = round($totalNPV - $period0Outflow, 2);
            // PPE Initial Balance = Total NPV
            $initialRouAsset = $totalNPV;
            // Straight-line monthly depreciation = Total NPV / totalMonths
            $monthlyDepreciation = round($initialRouAsset / $totalMonths, 2);

            $currentObligation = $initialLeaseLiability;
            $currentRouWdv = $initialRouAsset;

            $sumFinanceExpense = 0.0;
            $sumLeasePayments = 0.0;
            $sumDepreciation = 0.0;

            // Max period index identifies the final regular lease month
            $maxPeriod = max(array_map(fn($cf) => $cf->periodIndex, $cashFlows));

            foreach ($cashFlows as $cf) {
                if ($cf->periodIndex === 0) {
                    // Period 0 is initial upfront payment; schedule fields remain null
                    continue;
                }

                $isLastPeriod = ($cf->periodIndex === $maxPeriod);

                // Opening lease obligation
                $cf->openingLeaseObligation = $currentObligation;

                if ($currentObligation > 0) {
                    // Finance expense = Opening lease obligation * monthly discount rate
                    $finExpense = round($currentObligation * $monthlyRate, 2);
                    $cf->financeExpense = $finExpense;
                    $sumFinanceExpense += $finExpense;

                    // Lease payment (principal repayment portion of rent)
                    if ($isLastPeriod) {
                        $principalPayment = $currentObligation;
                        $closingObligation = 0.0;
                    } else {
                        $tentativePrincipal = round($cf->netOutflow - $finExpense, 2);
                        if ($tentativePrincipal >= $currentObligation) {
                            $principalPayment = $currentObligation;
                            $closingObligation = 0.0;
                        } elseif ($tentativePrincipal > 0) {
                            $principalPayment = $tentativePrincipal;
                            $closingObligation = round($currentObligation - $principalPayment, 2);
                        } else {
                            $principalPayment = 0.0;
                            $closingObligation = $currentObligation;
                        }
                    }

                    $cf->leasePayment = $principalPayment;
                    $cf->closingLeaseObligation = $closingObligation;
                    $sumLeasePayments += $principalPayment;
                    $currentObligation = $closingObligation;
                } else {
                    $cf->financeExpense = 0.0;
                    $cf->leasePayment = 0.0;
                    $cf->closingLeaseObligation = 0.0;
                }

                // PPE movement (ROU Asset)
                // Opening balance
                $cf->rouOpeningBalance = $currentRouWdv;

                // Depreciation charged
                if ($isLastPeriod) {
                    $depr = $currentRouWdv;
                    $closingWdv = 0.0;
                } else {
                    $depr = min($currentRouWdv, $monthlyDepreciation);
                    $closingWdv = round($currentRouWdv - $depr, 2);
                }

                $cf->depreciationCharged = $depr;
                $cf->rouWdv = max(0.0, $closingWdv);
                $sumDepreciation += $depr;
                $currentRouWdv = $cf->rouWdv;
            }

            $totalFinanceExpense = round($sumFinanceExpense, 2);
            $totalLeasePayments = round($sumLeasePayments, 2);
            $totalDepreciation = round($sumDepreciation, 2);
        }

        return new NpvCalculationResult(
            agreement: $agreement,
            baseDate: $input->baseDate,
            expiryDate: $agreement->expiry_date ?? ($agreement->to_date ?? ''),
            totalMonths: $totalMonths,
            annualDiscountRate: $annualRate,
            monthlyDiscountRate: $monthlyRate,
            totalNPV: $totalNPV,
            totalUndiscountedOutflow: round($totalUndiscountedOutflow, 2),
            totalGrossRent: round($totalGrossRent, 2),
            totalAdvanceDeductions: round($totalAdvanceDeductions, 2),
            totalDepositRefunds: round($totalDepositRefunds, 2),
            absorbableAdvanceTotal: round($absorbableTotal, 2),
            nonAbsorbableDepositTotal: round($nonAbsorbableTotal, 2),
            cashFlows: $cashFlows,
            hasIfrs16Schedule: $hasIfrs16Schedule,
            initialLeaseLiability: $initialLeaseLiability,
            initialRouAsset: $initialRouAsset,
            totalFinanceExpense: $totalFinanceExpense,
            totalLeasePayments: $totalLeasePayments,
            totalDepreciation: $totalDepreciation,
            monthlyDepreciation: $monthlyDepreciation
        );
    }
}
