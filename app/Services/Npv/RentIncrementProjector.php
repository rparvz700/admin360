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
     * @return array [effective_gross, multiplier, active_increments, cycle_no, total_cycles,
     *                starts_this_month, effective_from, uplift_pct]
     */
    public function calculateEffectiveGross(
        float $baseGrossRent,
        iterable $increments,
        string $billingMonth
    ): array {
        // Sort increments by start date ascending. The ordinal position within
        // this sorted list IS the increment cycle number (1st, 2nd, 3rd...) as
        // understood by the lease schedule.
        $sortedIncrements = collect($increments)
            ->filter(fn($inc) => $this->readValue($inc, 'increment_start_date') !== null)
            ->sortBy(fn($inc) => $this->readValue($inc, 'increment_start_date'))
            ->values();

        $totalCycles = $sortedIncrements->count();

        if ($baseGrossRent <= 0) {
            return [
                'effective_gross' => 0.0,
                'multiplier' => 1.0,
                'active_increments' => [],
                'cycle_no' => 0,
                'total_cycles' => $totalCycles,
                'starts_this_month' => false,
                'effective_from' => null,
                'uplift_pct' => 0.0,
            ];
        }

        $monthStart = Carbon::parse($billingMonth . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $multiplier = 1.0;
        $activeIncrements = [];
        $cycleNo = 0;
        $startsThisMonth = false;
        $effectiveFrom = null;

        foreach ($sortedIncrements as $ordinal => $inc) {
            $incStart = Carbon::parse($this->readValue($inc, 'increment_start_date'))->startOfMonth();

            // An increment is active if its start date is on or before the billing month end
            if (!$incStart->lte($monthEnd)) {
                continue;
            }

            // Cycle number = 1-based position in the chronological increment list.
            $cycle = $ordinal + 1;
            $incStartsThisMonth = $incStart->equalTo($monthStart);

            $pct = (float) ($this->readValue($inc, 'increment_percentage') ?? 0);

            if ($pct > 0) {
                $multiplier *= (1.0 + ($pct / 100.0));
                $activeIncrements[] = [
                    'cycle' => $cycle,
                    'start_date' => $incStart->format('Y-m-d'),
                    'percentage' => $pct,
                    'starts_this_month' => $incStartsThisMonth,
                    'cumulative_multiplier' => $multiplier,
                ];
            } else {
                // Alternative: flat amount increment if percentage is not defined
                $flatAmount = (float) ($this->readValue($inc, 'increment_amount') ?? 0);
                if ($flatAmount <= 0) {
                    continue;
                }

                $equivalentPct = ($flatAmount / $baseGrossRent) * 100.0;
                $multiplier *= (1.0 + ($equivalentPct / 100.0));
                $activeIncrements[] = [
                    'cycle' => $cycle,
                    'start_date' => $incStart->format('Y-m-d'),
                    'amount' => $flatAmount,
                    'equivalent_pct' => round($equivalentPct, 2),
                    'starts_this_month' => $incStartsThisMonth,
                    'cumulative_multiplier' => $multiplier,
                ];
            }

            // Track the latest applied cycle - that is the one currently in force.
            $cycleNo = $cycle;
            $startsThisMonth = $incStartsThisMonth;
            $effectiveFrom = $incStart->format('Y-m-d');
        }

        $effectiveGross = round($baseGrossRent * $multiplier, 2);

        return [
            'effective_gross' => $effectiveGross,
            'multiplier' => $multiplier,
            'active_increments' => $activeIncrements,
            'cycle_no' => $cycleNo,
            'total_cycles' => $totalCycles,
            'starts_this_month' => $startsThisMonth,
            'effective_from' => $effectiveFrom,
            'uplift_pct' => round(($multiplier - 1.0) * 100.0, 2),
        ];
    }

    /**
     * Read a field from an increment supplied as either an array or an object.
     */
    private function readValue(mixed $inc, string $key): mixed
    {
        if (is_array($inc)) {
            return $inc[$key] ?? null;
        }

        return $inc->{$key} ?? null;
    }
}
