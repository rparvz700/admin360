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
            ], $this->cashFlows),
        ];
    }
}
