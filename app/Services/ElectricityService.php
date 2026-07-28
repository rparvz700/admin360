<?php

namespace App\Services;

use App\Models\ElectricityBill;
use App\Models\ElectricityMeter;

class ElectricityService
{
    /**
     * Get the latest current reading for a given meter to use as previous reading.
     */
    public function getPreviousReading(int $meterId): float
    {
        $latestBill = ElectricityBill::where('meter_id', $meterId)
            ->where('bill_type', 'postpaid')
            ->orderBy('id', 'desc')
            ->first();

        return $latestBill ? (float) $latestBill->current_reading : 0.00;
    }

    /**
     * Calculate consumption units and totals for postpaid bill.
     */
    public function calculatePostpaidTotals(float $prevReading, float $currReading, float $rate, float $vat = 0): array
    {
        $units = max(0, $currReading - $prevReading);
        $netAmount = $units * $rate;
        $totalAmount = $netAmount + $vat;

        return [
            'units_consumed' => $units,
            'net_amount'     => $netAmount,
            'vat_amount'     => $vat,
            'total_amount'   => $totalAmount,
        ];
    }
}
