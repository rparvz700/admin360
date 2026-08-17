<?php

namespace App\Services\Npv;

use Carbon\Carbon;

class RentIncrementProjector
{
    /**
     * Apply compounded rent increments to a base gross rent amount for a specific month.
     *
     * @param float $baseGrossRent Initial un-incremented gross rent amount
     * @param iterable $increments List of increment objects/arrays with increment_start_date, increment_percentage, increment_amount
     * @param string $billingMonth Format: YYYY-MM (e.g. 2026-08)
     * @return array [effective_gross_rent, compound_multiplier, active_increments_info]
     */
    public function calculateEffectiveGross(
        float $baseGrossRent,
        iterable $increments,
        string $billingMonth
    ): array {
        if ($baseGrossRent <= 0) {
            return [
                'effective_gross' => 0.0,
                'multiplier' => 1.0,
                'active_increments' => [],
            ];
        }

        $monthStart = Carbon::parse($billingMonth . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $multiplier = 1.0;
        $activeIncrements = [];

        // Sort increments by start date ascending
        $sortedIncrements = collect($increments)->sortBy('increment_start_date');

        foreach ($sortedIncrements as $inc) {
            $startDate = is_array($inc) ? ($inc['increment_start_date'] ?? null) : ($inc->increment_start_date ?? null);
            if (!$startDate) {
                continue;
            }

            $incStart = Carbon::parse($startDate)->startOfMonth();

            // An increment is active if its start date is on or before the billing month end
            if ($incStart->lte($monthEnd)) {
                $pct = is_array($inc) ? ($inc['increment_percentage'] ?? 0) : ($inc->increment_percentage ?? 0);
                $pct = (float) $pct;

                if ($pct > 0) {
                    $multiplier *= (1.0 + ($pct / 100.0));
                    $activeIncrements[] = [
                        'start_date' => $incStart->format('Y-m-d'),
                        'percentage' => $pct,
                    ];
                } else {
                    // Alternative: flat amount increment if percentage is not defined
                    $flatAmount = is_array($inc) ? ($inc['increment_amount'] ?? 0) : ($inc->increment_amount ?? 0);
                    $flatAmount = (float) $flatAmount;
                    if ($flatAmount > 0 && $baseGrossRent > 0) {
                        $equivalentPct = ($flatAmount / $baseGrossRent) * 100.0;
                        $multiplier *= (1.0 + ($equivalentPct / 100.0));
                        $activeIncrements[] = [
                            'start_date' => $incStart->format('Y-m-d'),
                            'amount' => $flatAmount,
                            'equivalent_pct' => round($equivalentPct, 2),
                        ];
                    }
                }
            }
        }

        $effectiveGross = round($baseGrossRent * $multiplier, 2);

        return [
            'effective_gross' => $effectiveGross,
            'multiplier' => $multiplier,
            'active_increments' => $activeIncrements,
        ];
    }
}
