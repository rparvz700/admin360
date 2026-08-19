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
        public array $cashFlows                // Array of MonthlyCashFlow DTOs
    ) {}

    public function toArray(): array
    {
        $latestRentBase = $this->agreement->relationLoaded('rentBases')
            ? $this->agreement->rentBases->sortByDesc('id')->first()
            : null;
        $latestSd = $this->agreement->relationLoaded('securityDeposits')
            ? $this->agreement->securityDeposits->sortByDesc('id')->first()
            : null;
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
            'total_npv' => round($this->totalNPV, 2),
            'total_undiscounted_outflow' => round($this->totalUndiscountedOutflow, 2),
            'total_gross_rent' => round($this->totalGrossRent, 2),
            'total_advance_deductions' => round($this->totalAdvanceDeductions, 2),
            'total_deposit_refunds' => round($this->totalDepositRefunds, 2),
            'absorbable_advance_total' => round($this->absorbableAdvanceTotal, 2),
            'non_absorbable_deposit_total' => round($this->nonAbsorbableDepositTotal, 2),
            'audit' => [
                'base_rent' => round($latestRentBase->base_rent ?? 0, 2),
                'is_at_source' => (bool) ($latestRentBase->is_at_source ?? false),
                'vat' => round($latestRentBase->vat ?? 0, 2),
                'tax' => round($latestRentBase->tax ?? 0, 2),
                'components' => ($latestRentBase && $latestRentBase->components) ? $latestRentBase->components->map(fn($c) => [
                    'type' => $c->component_type,
                    'area' => round($c->area_sft, 2),
                    'rent' => round($c->rent_amount, 2),
                    'vat' => round($c->vat_amount ?? 0, 2),
                    'tax' => round($c->tax_amount ?? 0, 2),
                    'total' => round($c->total_amount, 2),
                ])->values()->all() : [],
                'increments' => $increments->map(fn($inc, $idx) => [
                    'cycle' => $idx + 1,
                    'start_date' => $inc->increment_start_date,
                    'percentage' => $inc->increment_percentage ? round($inc->increment_percentage, 2) : null,
                    'amount' => $inc->increment_amount ? round($inc->increment_amount, 2) : null,
                    'incremented_amount' => round($inc->incremented_amount ?? 0, 2),
                ])->values()->all(),
                'sd_total' => round($latestSd->security_deposit_total ?? 0, 2),
                'sd_absorbable' => round($latestSd->security_deposit_absorbable ?? 0, 2),
                'sd_non_absorbable' => round($latestSd->security_deposit_non_absorbable ?? 0, 2),
                'sd_frequency' => $latestSd->absorb_frequency ?? null,
                'sd_start_date' => $latestSd->absorb_start_date ?? null,
            ],
            'cash_flows' => array_map(fn(MonthlyCashFlow $cf) => [
                'period_index' => $cf->periodIndex,
                'billing_month' => $cf->billingMonth,
                'month_label' => $cf->monthLabel,
                'office_gross_rent' => round($cf->officeGrossRent, 2),
                'dg_gross_rent' => round($cf->dgGrossRent, 2),
                'parking_gross_rent' => round($cf->parkingGrossRent, 2),
                'store_gross_rent' => round($cf->storeGrossRent, 2),
                'total_gross_rent' => round($cf->totalGrossRent, 2),
                'advance_deduction' => round($cf->advanceDeduction, 2),
                'deposit_refund' => round($cf->depositRefund, 2),
                'net_outflow' => round($cf->netOutflow, 2),
                'discount_factor' => round($cf->discountFactor, 6),
                'present_value' => round($cf->presentValue, 2),
                'cumulative_pv' => round($cf->cumulativePV, 2),
                'active_increments' => $cf->activeIncrements,
                'increment_cycle' => $cf->incrementCycle,
                'total_increment_cycles' => $cf->totalIncrementCycles,
                'increment_starts_this_month' => $cf->incrementStartsThisMonth,
                'increment_effective_from' => $cf->incrementEffectiveFrom,
                'increment_uplift_pct' => round($cf->incrementUpliftPct, 2),
            ], $this->cashFlows),
        ];
    }
}
