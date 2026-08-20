<?php

namespace App\Services\Npv;

use App\Models\SecurityDeposit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdvanceAdjustmentScheduler
{
    /**
     * Determine advance deduction and non-absorbable deposit refund for a given month.
     *
     * @param Collection|iterable $securityDeposits SecurityDeposit records for the agreement
     * @param string $billingMonth Format: YYYY-MM
     * @param string|null $agreementFromDate Agreement start date (fallback for absorb_start_date)
     * @param string|null $agreementToDate Agreement expiry date (fallback for absorb_end_date)
     * @param bool $isExpiryMonth Whether this billing month is the final contract expiry month
     * @return array [advance_deduction, deposit_refund, details]
     */
    public function getAdjustmentsForMonth(
        iterable $securityDeposits,
        string $billingMonth,
        ?string $agreementFromDate = null,
        ?string $agreementToDate = null,
        bool $isExpiryMonth = false
    ): array {
        $monthStart = Carbon::parse($billingMonth . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $deposits = collect($securityDeposits);
        if ($deposits->isEmpty()) {
            return [
                'advance_deduction' => 0.0,
                'deposit_refund' => 0.0,
                'details' => [],
            ];
        }

        // 1. Extract unique summary values (prevent duplicate row aggregation)
        $firstDeposit = $deposits->first();
        $absorbableTotal = max(
            (float) ($firstDeposit->security_deposit_absorbable ?? 0),
            (float) $deposits->max('security_deposit_absorbable'),
            (float) $deposits->sum('absorb_amount')
        );
        $nonAbsorbableTotal = max(
            (float) ($firstDeposit->security_deposit_non_absorbable ?? 0),
            (float) $deposits->max('security_deposit_non_absorbable')
        );

        // Fallback total agreement duration in months
        $fallbackStart = $agreementFromDate ? Carbon::parse($agreementFromDate)->startOfMonth() : null;
        $fallbackEnd = $agreementToDate ? Carbon::parse($agreementToDate)->endOfMonth() : null;
        $fallbackMonths = ($fallbackStart && $fallbackEnd)
            ? max(1, $fallbackStart->diffInMonths($fallbackEnd) + 1)
            : 120; // default fallback 120 months

        $totalAdvanceDeduction = 0.0;
        $details = [];

        // Filter schedule rows that have explicit schedule parameters
        $scheduleRows = $deposits->filter(function ($sd) {
            return ($sd->absorb_amount > 0 || $sd->absorb_start_date !== null || $sd->absorb_frequency > 0);
        });

        if ($scheduleRows->isNotEmpty()) {
            $prevEndDate = null;

            foreach ($scheduleRows as $sd) {
                $absorbAmount = (float) ($sd->absorb_amount ?? 0);
                $monthsInterval = (int) ($sd->absorb_frequency ?? 0);

                if ($monthsInterval <= 0) {
                    $monthsInterval = $fallbackMonths;
                }

                // Determine start date for this clause (chains sequentially if absorb_start_date is omitted)
                if ($sd->absorb_start_date) {
                    $absStart = Carbon::parse($sd->absorb_start_date)->startOfMonth();
                } elseif ($prevEndDate !== null) {
                    $absStart = $prevEndDate->copy()->addMonth()->startOfMonth();
                } elseif ($agreementFromDate) {
                    $absStart = Carbon::parse($agreementFromDate)->startOfMonth();
                } else {
                    $absStart = null;
                }

                // Determine end date for this clause
                if ($sd->absorb_end_date) {
                    $absEnd = Carbon::parse($sd->absorb_end_date)->endOfMonth();
                } elseif ($absStart && $monthsInterval > 0) {
                    $absEnd = $absStart->copy()->addMonths($monthsInterval - 1)->endOfMonth();
                } else {
                    $absEnd = $fallbackEnd;
                }

                if ($absEnd) {
                    $prevEndDate = $absEnd;
                }

                $isWithinRange = false;
                if ($absStart && $absEnd) {
                    if ($monthStart->gte($absStart) && $monthStart->lte($absEnd)) {
                        $isWithinRange = true;
                    }
                } elseif ($fallbackStart && $fallbackEnd) {
                    if ($monthStart->gte($fallbackStart) && $monthStart->lte($fallbackEnd)) {
                        $isWithinRange = true;
                    }
                }

                if ($isWithinRange) {
                    if ($absorbAmount > 0 && $monthsInterval > 0) {
                        $deduction = $absorbAmount / $monthsInterval;
                    } elseif ($absorbableTotal > 0 && $monthsInterval > 0) {
                        $deduction = $absorbableTotal / $monthsInterval;
                    } else {
                        $deduction = 0.0;
                    }

                    $totalAdvanceDeduction += $deduction;
                    $details[] = [
                        'type' => 'advance_deduction',
                        'amount' => round($deduction, 2),
                    ];
                }
            }
        } elseif ($absorbableTotal > 0) {
            // Summary-level fallback deduction (no specific schedule rows)
            $isWithinAgreement = true;
            if ($fallbackStart && $fallbackEnd) {
                $isWithinAgreement = ($monthStart->gte($fallbackStart) && $monthStart->lte($fallbackEnd));
            }

            if ($isWithinAgreement && $fallbackMonths > 0) {
                $deduction = $absorbableTotal / $fallbackMonths;
                $totalAdvanceDeduction += $deduction;
                $details[] = [
                    'type' => 'advance_deduction_summary',
                    'amount' => round($deduction, 2),
                ];
            }
        }

        // 2. Non-Absorbable Security Deposit Refund (Applied EXACTLY ONCE at contract expiry)
        $totalDepositRefund = 0.0;
        if ($isExpiryMonth && $nonAbsorbableTotal > 0) {
            $totalDepositRefund = $nonAbsorbableTotal;
            $details[] = [
                'type' => 'security_deposit_refund',
                'amount' => round($nonAbsorbableTotal, 2),
            ];
        }

        return [
            'advance_deduction' => round($totalAdvanceDeduction, 2),
            'deposit_refund' => round($totalDepositRefund, 2),
            'details' => $details,
        ];
    }
}
