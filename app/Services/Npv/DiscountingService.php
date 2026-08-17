<?php

namespace App\Services\Npv;

class DiscountingService
{
    /**
     * Convert an annual interest rate percentage into an effective monthly compounding rate.
     * Formula: r_monthly = (1 + r_annual)^(1/12) - 1
     *
     * @param float $annualRatePercent Annual rate as percentage (e.g. 12.16)
     * @return float Effective monthly rate as decimal (e.g. 0.00960628)
     */
    public function annualToMonthlyRate(float $annualRatePercent): float
    {
        if ($annualRatePercent <= 0) {
            return 0.0;
        }

        $annualDecimal = $annualRatePercent / 100.0;

        return pow(1.0 + $annualDecimal, 1.0 / 12.0) - 1.0;
    }

    /**
     * Calculate the discount factor for a given month number.
     * Formula: DF_t = 1 / (1 + r_monthly)^t
     *
     * @param int $monthIndex 1-indexed period number (1, 2, 3...)
     * @param float $monthlyRate Monthly compounding rate as decimal
     * @return float Discount factor
     */
    public function calculateDiscountFactor(int $monthIndex, float $monthlyRate): float
    {
        if ($monthIndex <= 0 || $monthlyRate < 0) {
            return 1.0;
        }

        return 1.0 / pow(1.0 + $monthlyRate, $monthIndex);
    }

    /**
     * Calculate Present Value for a monthly net cash outflow.
     * Formula: PV = Outflow * DiscountFactor
     *
     * @param float $netOutflow Net cash outflow for the month
     * @param float $discountFactor Discount factor for the month
     * @return float Present value
     */
    public function calculatePresentValue(float $netOutflow, float $discountFactor): float
    {
        return round($netOutflow * $discountFactor, 4);
    }
}
