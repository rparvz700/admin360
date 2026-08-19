<?php

namespace App\Services\Npv;

use App\Models\Agreement;
use App\Models\FinanceSetting;
use App\Models\NpvAgreementSummary;
use App\Services\Npv\Dto\NpvCalculationInput;
use App\Services\Npv\Dto\NpvCalculationResult;
use App\Services\Npv\Dto\NpvSummaryRow;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class NpvReportService
{
    private const CACHE_PREFIX = 'npv_summary_v2_';
    private const CACHE_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private NpvCalculationService $npvService
    ) {}

    /**
     * Generate summary rows for all active agreements using DB table storage (Strategy 2).
     *
     * @param float $annualDiscountRate Override rate or 0 for default
     * @param bool $forceRefresh Flush cache and re-calculate
     * @return NpvSummaryRow[]
     */
    public function generateSummaryForAll(float $annualDiscountRate = 0.0, bool $forceRefresh = false): array
    {
        $rate = $annualDiscountRate > 0
            ? $annualDiscountRate
            : FinanceSetting::getValue('npv_annual_discount_rate', 12.16);

        $this->ensureTableExists();

        if ($forceRefresh) {
            $this->clearCacheForRate($rate);
        }

        // Check active agreement IDs against cached summary IDs for this discount_rate
        $activeAgreementIds = Agreement::where('status', 1)->pluck('id')->toArray();
        $cachedAgreementIds = NpvAgreementSummary::where('discount_rate', $rate)->pluck('agreement_id')->toArray();

        $missingIds = array_diff($activeAgreementIds, $cachedAgreementIds);

        // If all active agreements have up-to-date summaries for this rate, fetch directly from DB in < 3ms
        if (!$forceRefresh && empty($missingIds) && count($cachedAgreementIds) === count($activeAgreementIds)) {
            $cachedRows = NpvAgreementSummary::where('discount_rate', $rate)
                ->whereIn('agreement_id', $activeAgreementIds)
                ->orderBy('total_npv', 'desc')
                ->get();

            return $cachedRows->map(fn($row) => new NpvSummaryRow(
                agreementId: $row->agreement_id,
                agreementRefNo: $row->agreement_ref_no,
                vendorName: $row->vendor_name ?? 'N/A',
                siteName: $row->site_name ?? 'N/A',
                fromDate: $row->from_date ?? 'N/A',
                toDate: $row->to_date ?? 'N/A',
                totalMonths: $row->total_months,
                totalNPV: (float) $row->total_npv,
                totalUndiscountedOutflow: (float) $row->total_undiscounted_outflow,
                totalGrossRent: (float) $row->total_gross_rent,
                totalAdvanceDeductions: (float) $row->total_advance_deductions,
                totalDepositRefunds: (float) $row->total_deposit_refunds,
                annualDiscountRate: (float) $row->discount_rate
            ))->all();
        }

        // Calculate and persist summaries in bulk for this rate
        $agreements = Agreement::with([
            'vendor',
            'floors.building',
            'rentBases' => fn($q) => $q->orderBy('id', 'desc')->with('components'),
            'rentIncrements',
            'securityDeposits' => fn($q) => $q->orderBy('id', 'desc'),
        ])
        ->where('status', 1)
        ->orderBy('id', 'desc')
        ->get();

        $rows = [];
        $upsertData = [];
        $now = now();

        foreach ($agreements as $agr) {
            $baseDate = $agr->from_date ?: now()->startOfMonth()->format('Y-m-d');
            $input = new NpvCalculationInput(
                agreementId: $agr->id,
                baseDate: $baseDate,
                annualDiscountRate: $rate
            );

            try {
                $result = $this->npvService->calculateForAgreement($agr, $input);
                $firstFloor = $agr->floors->first();
                $siteName = $firstFloor?->building?->site_name
                    ?? ($firstFloor?->building?->code ?? 'N/A');

                $summaryData = [
                    'agreement_id' => $agr->id,
                    'discount_rate' => $rate,
                    'agreement_ref_no' => $agr->agreement_ref_no ?? ('AGR-' . $agr->id),
                    'vendor_name' => $agr->vendor->name ?? 'N/A',
                    'site_name' => $siteName,
                    'from_date' => $agr->from_date ?? 'N/A',
                    'to_date' => $agr->to_date ?? 'N/A',
                    'total_months' => $result->totalMonths,
                    'total_npv' => round($result->totalNPV, 2),
                    'total_undiscounted_outflow' => round($result->totalUndiscountedOutflow, 2),
                    'total_gross_rent' => round($result->totalGrossRent, 2),
                    'total_advance_deductions' => round($result->totalAdvanceDeductions, 2),
                    'total_deposit_refunds' => round($result->totalDepositRefunds, 2),
                    'calculated_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $upsertData[] = $summaryData;

                $rows[] = new NpvSummaryRow(
                    agreementId: $summaryData['agreement_id'],
                    agreementRefNo: $summaryData['agreement_ref_no'],
                    vendorName: $summaryData['vendor_name'],
                    siteName: $summaryData['site_name'],
                    fromDate: $summaryData['from_date'],
                    toDate: $summaryData['to_date'],
                    totalMonths: $summaryData['total_months'],
                    totalNPV: $summaryData['total_npv'],
                    totalUndiscountedOutflow: $summaryData['total_undiscounted_outflow'],
                    totalGrossRent: $summaryData['total_gross_rent'],
                    totalAdvanceDeductions: $summaryData['total_advance_deductions'],
                    totalDepositRefunds: $summaryData['total_deposit_refunds'],
                    annualDiscountRate: $summaryData['discount_rate']
                );
            } catch (\Exception $e) {
                // Continue calculating remaining agreements if 1 fails
            }
        }

        // Bulk upsert into npv_agreement_summaries table for Strategy 2
        if (!empty($upsertData)) {
            NpvAgreementSummary::upsert($upsertData, ['agreement_id', 'discount_rate'], [
                'agreement_ref_no',
                'vendor_name',
                'site_name',
                'from_date',
                'to_date',
                'total_months',
                'total_npv',
                'total_undiscounted_outflow',
                'total_gross_rent',
                'total_advance_deductions',
                'total_deposit_refunds',
                'calculated_at',
                'updated_at'
            ]);
        }

        // Sort descending by Total NPV
        usort($rows, fn($a, $b) => $b->totalNPV <=> $a->totalNPV);

        return $rows;
    }

    /**
     * Fetch full calculation result for an agreement (for AJAX detail modal).
     */
    public function getDetailBreakdown(int $agreementId, float $annualDiscountRate = 0.0): NpvCalculationResult
    {
        $agreement = Agreement::with([
            'vendor',
            'floors.building',
            'rentBases' => fn($q) => $q->orderBy('id', 'desc')->with('components'),
            'rentIncrements',
            'securityDeposits' => fn($q) => $q->orderBy('id', 'desc'),
        ])->findOrFail($agreementId);

        $rate = $annualDiscountRate > 0
            ? $annualDiscountRate
            : FinanceSetting::getValue('npv_annual_discount_rate', 12.16);

        $baseDate = $agreement->from_date ?: now()->startOfMonth()->format('Y-m-d');
        $input = new NpvCalculationInput(
            agreementId: $agreement->id,
            baseDate: $baseDate,
            annualDiscountRate: $rate
        );

        return $this->npvService->calculateForAgreement($agreement, $input);
    }

    /**
     * Clear cached NPV summary records for a specific rate or all rates.
     */
    public function clearCache(): void
    {
        $this->ensureTableExists();
        NpvAgreementSummary::truncate();
    }

    /**
     * Clear cached summary rows for a specific discount rate.
     */
    public function clearCacheForRate(float $discountRate): void
    {
        $this->ensureTableExists();
        NpvAgreementSummary::where('discount_rate', $discountRate)->delete();
    }

    /**
     * Helper to ensure table exists in database.
     */
    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('npv_agreement_summaries')) {
            Schema::create('npv_agreement_summaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agreement_id');
                $table->decimal('discount_rate', 5, 2);
                $table->string('agreement_ref_no');
                $table->string('vendor_name')->nullable();
                $table->string('site_name')->nullable();
                $table->string('from_date', 50)->nullable();
                $table->string('to_date', 50)->nullable();
                $table->integer('total_months')->default(0);
                $table->decimal('total_npv', 18, 2)->default(0.00);
                $table->decimal('total_undiscounted_outflow', 18, 2)->default(0.00);
                $table->decimal('total_gross_rent', 18, 2)->default(0.00);
                $table->decimal('total_advance_deductions', 18, 2)->default(0.00);
                $table->decimal('total_deposit_refunds', 18, 2)->default(0.00);
                $table->timestamp('calculated_at')->nullable();
                $table->timestamps();

                $table->unique(['agreement_id', 'discount_rate'], 'npv_agr_rate_unique');
                $table->index(['discount_rate', 'total_npv'], 'npv_rate_val_idx');
            });
        }
    }
}
