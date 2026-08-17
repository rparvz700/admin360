<?php

namespace App\Services\Npv\Dto;

class MonthlyCashFlow
{
    public function __construct(
        public int $periodIndex,          // 1-indexed (1, 2, 3...)
        public string $billingMonth,      // Format: YYYY-MM (e.g. 2025-11)
        public string $monthLabel,        // Format: Nov 2025
        public float $officeGrossRent,    // Gross office floor rent
        public float $dgGrossRent,        // Gross DG space rent
        public float $parkingGrossRent,   // Gross car parking rent
        public float $storeGrossRent,     // Gross store space rent
        public float $totalGrossRent,     // Sum of all gross component rents
        public float $advanceDeduction,   // Advance adjustment (- value)
        public float $depositRefund,      // Non-absorbable SD refund at expiry (- value)
        public float $netOutflow,         // totalGrossRent - advanceDeduction - depositRefund
        public float $discountFactor,     // 1 / (1 + monthlyRate)^t
        public float $presentValue,       // netOutflow * discountFactor
        public float $cumulativePV,       // Running cumulative PV
        public array $activeIncrements = [] // Description of active increments
    ) {}
}
