<?php

namespace App\Services\Npv\Dto;

class NpvSummaryRow
{
    public function __construct(
        public int $agreementId,
        public string $agreementRefNo,
        public string $vendorName,
        public string $siteName,
        public string $paymentStartDate,
        public string $expiryDate,
        public int $totalMonths,
        public float $totalNPV,
        public float $totalUndiscountedOutflow,
        public float $totalGrossRent,
        public float $totalAdvanceDeductions,
        public float $totalDepositRefunds,
        public float $annualDiscountRate,
        public ?string $fromDate = null,
        public ?string $toDate = null
    ) {
        $this->fromDate = $this->fromDate ?? $this->paymentStartDate;
        $this->toDate = $this->toDate ?? $this->expiryDate;
    }

    public function toArray(): array
    {
        return [
            'agreement_id' => $this->agreementId,
            'agreement_ref_no' => $this->agreementRefNo,
            'vendor_name' => $this->vendorName,
            'site_name' => $this->siteName,
            'payment_start_date' => $this->paymentStartDate,
            'expiry_date' => $this->expiryDate,
            'from_date' => $this->paymentStartDate,
            'to_date' => $this->expiryDate,
            'total_months' => $this->totalMonths,
            'total_npv' => $this->totalNPV,
            'total_undiscounted_outflow' => $this->totalUndiscountedOutflow,
            'total_gross_rent' => $this->totalGrossRent,
            'total_advance_deductions' => $this->totalAdvanceDeductions,
            'total_deposit_refunds' => $this->totalDepositRefunds,
            'annual_discount_rate' => $this->annualDiscountRate,
        ];
    }
}
