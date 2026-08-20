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
        public array $activeIncrements = [], // Description of active increments
        public int $incrementCycle = 0,     // Which increment cycle is in force this month (0 = base rent, no increment yet)
        public int $totalIncrementCycles = 0, // Total increment cycles defined on the agreement
        public bool $incrementStartsThisMonth = false, // True only on the month the cycle first takes effect
        public ?string $incrementEffectiveFrom = null, // Start date (Y-m-d) of the cycle in force
        public float $incrementUpliftPct = 0.0, // Compounded uplift over base rent, in %
        public bool $isDeferred = false, // True if month is in deferred payment period
        public float $arrearsAmount = 0.0 // Cumulative arrears included in this month (first payment month)
    ) {}
}
