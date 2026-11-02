<?php

namespace App\Services\Npv\Dto;

use App\Models\Agreement;

class NpvCalculationResult
{
    /**
     * @param MonthlyCashFlow[] $cashFlows
     */
    public function __construct(
        public Agreement $agreement,
        public string $baseDate,
        public string $expiryDate,
        public int $totalMonths,
        public float $annualDiscountRate,
        public float $monthlyDiscountRate,
        public float $totalNPV,                // Sum of discounted present values
        public float $totalUndiscountedOutflow, // Sum of net outflows
        public float $totalGrossRent,          // Sum of gross rents
        public float $totalAdvanceDeductions,  // Sum of advance deductions
        public float $totalDepositRefunds,     // Total refundable security deposit
        public float $absorbableAdvanceTotal,  // Initial absorbable security deposit / advance
        public float $nonAbsorbableDepositTotal,// Initial non-absorbable security deposit
        public array $cashFlows,               // Array of MonthlyCashFlow DTOs
        public bool $hasIfrs16Schedule = false,
        public ?float $initialLeaseLiability = null,
        public ?float $initialRouAsset = null,
        public ?float $totalFinanceExpense = null,
        public ?float $totalLeasePayments = null,
        public ?float $totalDepreciation = null,
        public ?float $monthlyDepreciation = null
    ) {}

    public function toArray(): array
    {
        $latestRentBase = $this->agreement->relationLoaded('rentBases')
            ? $this->agreement->rentBases->sortByDesc('id')->first()
            : null;
        $securityDeposits = $this->agreement->relationLoaded('securityDeposits')
            ? $this->agreement->securityDeposits
            : collect();
        $latestSd = $securityDeposits->sortByDesc('id')->first();
        $sdAbsorbable = max(
            (float) ($latestSd->security_deposit_absorbable ?? 0),
            (float) $securityDeposits->max('security_deposit_absorbable'),
            (float) $securityDeposits->sum('absorb_amount')
        );
        $sdNonAbsorbable = max(
            (float) ($latestSd->security_deposit_non_absorbable ?? 0),
            (float) $securityDeposits->max('security_deposit_non_absorbable')
        );
        $sdTotal = max(
            (float) ($latestSd->security_deposit_total ?? 0),
            (float) $securityDeposits->max('security_deposit_total'),
            $sdAbsorbable + $sdNonAbsorbable
        );
        $increments = $this->agreement->relationLoaded('rentIncrements')
            ? $this->agreement->rentIncrements->sortBy('increment_start_date')
            : collect();

        return [
            'agreement_ref' => $this->agreement->agreement_ref_no,
            'vendor_name' => $this->agreement->vendor->name ?? 'N/A',
            'base_date' => $this->baseDate,
            'expiry_date' => $this->expiryDate,
            'total_months' => $this->totalMonths,
            'annual_discount_rate' => $this->annualDiscountRate,
            'monthly_discount_rate' => round($this->monthlyDiscountRate * 100, 6),
            'total_npv' => (float) round($this->totalNPV),
            'total_undiscounted_outflow' => (float) round($this->totalUndiscountedOutflow),
            'total_gross_rent' => (float) round($this->totalGrossRent),
            'total_advance_deductions' => (float) round($this->totalAdvanceDeductions),
            'total_deposit_refunds' => (float) round($this->totalDepositRefunds),
            'absorbable_advance_total' => (float) round($this->absorbableAdvanceTotal),
            'non_absorbable_deposit_total' => (float) round($this->nonAbsorbableDepositTotal),
            'has_ifrs16_schedule' => $this->hasIfrs16Schedule,
            'ifrs16' => $this->hasIfrs16Schedule ? [
                'opening_lease_obligation' => (float) round($this->initialLeaseLiability ?? 0),
                'lease_payment' => (float) round($this->totalLeasePayments ?? 0),
                'closing_lease_obligation' => 0.0,
                'finance_expense' => (float) round($this->totalFinanceExpense ?? 0),
                'ppe_opening_balance' => (float) round($this->initialRouAsset ?? 0),
                'depreciation_charged' => (float) round($this->totalDepreciation ?? 0),
                'wdv' => 0.0,
                'initial_lease_liability' => (float) round($this->initialLeaseLiability ?? 0),
                'initial_rou_asset' => (float) round($this->initialRouAsset ?? 0),
                'monthly_depreciation' => (float) round($this->monthlyDepreciation ?? 0),
            ] : null,
            'audit' => [
                'base_rent' => (float) round($latestRentBase->base_rent ?? 0),
                'is_at_source' => (bool) ($latestRentBase->is_at_source ?? false),
                'vat' => (float) round($latestRentBase->vat ?? 0),
                'tax' => (float) round($latestRentBase->tax ?? 0),
                'components' => ($latestRentBase && $latestRentBase->components) ? $latestRentBase->components->map(fn($c) => [
                    'type' => $c->component_type,
                    'area' => round($c->area_sft, 2),
                    'rent' => (float) round($c->rent_amount),
                    'vat' => (float) round($c->vat_amount ?? 0),
                    'tax' => (float) round($c->tax_amount ?? 0),
                    'total' => (float) round($c->total_amount),
                ])->values()->all() : [],
                'increments' => $increments->map(fn($inc, $idx) => [
                    'cycle' => $idx + 1,
                    'start_date' => $inc->increment_start_date,
                    'percentage' => $inc->increment_percentage ? round($inc->increment_percentage, 2) : null,
                    'amount' => $inc->increment_amount ? (float) round($inc->increment_amount) : null,
                    'incremented_amount' => (float) round($inc->incremented_amount ?? 0),
                ])->values()->all(),
                'sd_total' => (float) round($sdTotal),
                'sd_absorbable' => (float) round($sdAbsorbable),
                'sd_non_absorbable' => (float) round($sdNonAbsorbable),
                'sd_frequency' => $latestSd->absorb_frequency ?? null,
                'sd_start_date' => $latestSd->absorb_start_date ?? null,
                'sd_clauses' => $securityDeposits->map(fn($sd) => [
                    'absorb_amount' => (float) round($sd->absorb_amount ?? 0),
                    'frequency' => $sd->absorb_frequency,
                    'start_date' => $sd->absorb_start_date,
                    'end_date' => $sd->absorb_end_date,
                    'description' => $sd->method_description,
                ])->values()->all(),
            ],
            'cash_flows' => array_map(fn(MonthlyCashFlow $cf) => [
                'period_index' => $cf->periodIndex,
                'billing_month' => $cf->billingMonth,
                'month_label' => $cf->monthLabel,
                'office_gross_rent' => (float) round($cf->officeGrossRent),
                'dg_gross_rent' => (float) round($cf->dgGrossRent),
                'parking_gross_rent' => (float) round($cf->parkingGrossRent),
                'store_gross_rent' => (float) round($cf->storeGrossRent),
                'total_gross_rent' => (float) round($cf->totalGrossRent),
                'advance_deduction' => (float) round($cf->advanceDeduction),
                'deposit_refund' => (float) round($cf->depositRefund),
                'net_outflow' => (float) round($cf->netOutflow),
                'discount_factor' => round($cf->discountFactor, 4),
                'present_value' => (float) round($cf->presentValue),
                'cumulative_pv' => (float) round($cf->cumulativePV),
                'active_increments' => $cf->activeIncrements,
                'increment_cycle' => $cf->incrementCycle,
                'total_increment_cycles' => $cf->totalIncrementCycles,
                'increment_starts_this_month' => $cf->incrementStartsThisMonth,
                'increment_effective_from' => $cf->incrementEffectiveFrom,
                'increment_uplift_pct' => round($cf->incrementUpliftPct, 2),
                'is_deferred' => $cf->isDeferred,
                'arrears_amount' => (float) round($cf->arrearsAmount),
                'opening_lease_obligation' => $cf->openingLeaseObligation !== null ? (float) round($cf->openingLeaseObligation) : null,
                'lease_payment' => $cf->leasePayment !== null ? (float) round($cf->leasePayment) : null,
                'closing_lease_obligation' => $cf->closingLeaseObligation !== null ? (float) round($cf->closingLeaseObligation) : null,
                'finance_expense' => $cf->financeExpense !== null ? (float) round($cf->financeExpense) : null,
                'ppe_opening_balance' => $cf->rouOpeningBalance !== null ? (float) round($cf->rouOpeningBalance) : null,
                'rou_opening_balance' => $cf->rouOpeningBalance !== null ? (float) round($cf->rouOpeningBalance) : null,
                'depreciation_charged' => $cf->depreciationCharged !== null ? (float) round($cf->depreciationCharged) : null,
                'wdv' => $cf->rouWdv !== null ? (float) round($cf->rouWdv) : null,
                'rou_wdv' => $cf->rouWdv !== null ? (float) round($cf->rouWdv) : null,
            ], $this->cashFlows),
        ];
    }
}
