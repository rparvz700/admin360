<?php

namespace App\Services\Npv\Dto;

class NpvCalculationInput
{
    public function __construct(
        public int $agreementId,
        public string $baseDate, // Format: YYYY-MM-DD or YYYY-MM
        public float $annualDiscountRate = 12.16,
        public ?float $customTaxRate = null,
        public ?float $customVatRate = null,
    ) {}
}
