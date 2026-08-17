<?php

namespace App\Services\Npv;

class RentGrossUpCalculator
{
    /**
     * Calculate gross rent for a component based on area, tax, VAT, and is_at_source flag.
     *
     * @param float $netRent Contracted net rent amount for component
     * @param float $areaSft Area in square feet
     * @param bool $isAtSource Whether tax is deducted at source (requires division gross-up)
     * @param float $taxPercent Tax percentage (e.g. 10.0 for 10%)
     * @param float $vatPercent VAT percentage (e.g. 15.0 for 15%)
     * @param float $taxableThreshold Area threshold for VAT applicability (default: 150 sq ft)
     * @return array Breakdown containing gross_before_vat, vat_amount, tax_amount, total_gross
     */
    public function calculateComponentGross(
        float $netRent,
        float $areaSft,
        bool $isAtSource,
        float $taxPercent,
        float $vatPercent,
        float $taxableThreshold = 150.0
    ): array {
        if ($netRent <= 0) {
            return [
                'net_rent' => 0.0,
                'gross_before_vat' => 0.0,
                'tax_amount' => 0.0,
                'vat_amount' => 0.0,
                'total_gross' => 0.0,
                'vat_applicable' => false,
            ];
        }

        $taxDecimal = $taxPercent / 100.0;
        $vatDecimal = $vatPercent / 100.0;

        // 1. Calculate Gross Before VAT based on tax deduction method
        if ($isAtSource) {
            // When tax is at source, gross-up inflates net rent so landlord gets contracted net after deduction:
            // Net = Gross * (1 - Tax)  ==>  Gross = Net / (1 - Tax)
            $denominator = 1.0 - $taxDecimal;
            $grossBeforeVat = $denominator > 0 ? ($netRent / $denominator) : $netRent;
            $taxAmount = $grossBeforeVat - $netRent;
        } else {
            // Standard additive tax
            $taxAmount = $netRent * $taxDecimal;
            $grossBeforeVat = $netRent + $taxAmount;
        }

        // 2. VAT applicability based on area threshold (>= 150 sq ft)
        $vatApplicable = ($areaSft >= $taxableThreshold);
        $vatAmount = $vatApplicable ? ($grossBeforeVat * $vatDecimal) : 0.0;

        $totalGross = $grossBeforeVat + $vatAmount;

        return [
            'net_rent' => round($netRent, 2),
            'gross_before_vat' => round($grossBeforeVat, 2),
            'tax_amount' => round($taxAmount, 2),
            'vat_amount' => round($vatAmount, 2),
            'total_gross' => round($totalGross, 2),
            'vat_applicable' => $vatApplicable,
        ];
    }
}
