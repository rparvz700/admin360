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
        $this->ensureTableExists();

        if ($forceRefresh) {
            $this->clearCache();
        }

        // Fetch all active agreements
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

        $activeAgreementIds = $agreements->pluck('id')->toArray();

        // Invalidate stale cache records created before new NPV engine logic
        $staleCutoff = '2026-08-20 14:10:00';
        NpvAgreementSummary::where('calculated_at', '<', $staleCutoff)->delete();

        // Fetch existing cached records
        $cachedRecords = NpvAgreementSummary::whereIn('agreement_id', $activeAgreementIds)
            ->get()
            ->keyBy('agreement_id');

        $rows = [];
        $upsertData = [];
        $now = now();
        $hasPaymentStartCol = Schema::hasColumn('npv_agreement_summaries', 'payment_start_date');

        foreach ($agreements as $agr) {
            // Check agreement's individual NPV interest rate (or override parameter if explicitly provided)
            $agrConfiguredRate = (!is_null($agr->npv_interest_rate) && (float) $agr->npv_interest_rate > 0)
                ? (float) $agr->npv_interest_rate
                : null;

            $effectiveRate = ($annualDiscountRate > 0) ? $annualDiscountRate : $agrConfiguredRate;
            $hasRate = ($effectiveRate !== null && $effectiveRate > 0);
            $cachedKeyRate = $hasRate ? round($effectiveRate, 2) : 0.00;

            // Check if valid cache entry exists
            if (!$forceRefresh && $cachedRecords->has($agr->id)) {
                $cachedRow = $cachedRecords->get($agr->id);
                // Validate that cached discount_rate matches the agreement's current effective rate
                if (abs((float) $cachedRow->discount_rate - $cachedKeyRate) < 0.001) {
                    $rows[] = new NpvSummaryRow(
                        agreementId: $cachedRow->agreement_id,
                        agreementRefNo: $cachedRow->agreement_ref_no,
                        vendorName: $cachedRow->vendor_name ?? 'N/A',
                        siteName: $cachedRow->site_name ?? 'N/A',
                        paymentStartDate: $cachedRow->payment_start_date ?? ($cachedRow->from_date ?? 'N/A'),
                        expiryDate: $cachedRow->expiry_date ?? ($cachedRow->to_date ?? 'N/A'),
                        totalMonths: (int) $cachedRow->total_months,
                        totalNPV: $hasRate ? (float) $cachedRow->total_npv : null,
                        totalUndiscountedOutflow: (float) $cachedRow->total_undiscounted_outflow,
                        totalGrossRent: (float) $cachedRow->total_gross_rent,
                        totalAdvanceDeductions: (float) $cachedRow->total_advance_deductions,
                        totalDepositRefunds: (float) $cachedRow->total_deposit_refunds,
                        annualDiscountRate: $hasRate ? (float) $cachedRow->discount_rate : null,
                        hasInterestRate: $hasRate,
                        fromDate: $cachedRow->payment_start_date ?? ($cachedRow->from_date ?? 'N/A'),
                        toDate: $cachedRow->expiry_date ?? ($cachedRow->to_date ?? 'N/A')
                    );
                    continue;
                }
            }

            // Calculate for this agreement
            $baseDate = $agr->payment_start_date 
                ?? ($agr->from_date ?? ($agr->agreement_date ?: now()->startOfMonth()->format('Y-m-d')));

            $input = new NpvCalculationInput(
                agreementId: $agr->id,
                baseDate: $baseDate,
                annualDiscountRate: $hasRate ? $effectiveRate : 0.0
            );

            try {
                $result = $this->npvService->calculateForAgreement($agr, $input, allowZeroRate: true);
                $firstFloor = $agr->floors->first();
                $siteName = $firstFloor?->building?->site_name
                    ?? ($firstFloor?->building?->code ?? 'N/A');

                $paymentStartVal = $agr->payment_start_date ?? ($agr->from_date ?? 'N/A');
                $expiryVal = $agr->expiry_date ?? ($agr->to_date ?? 'N/A');

                $calculatedNpv = $hasRate ? round($result->totalNPV, 2) : null;

                $summaryData = [
                    'agreement_id' => $agr->id,
                    'discount_rate' => $cachedKeyRate,
                    'agreement_ref_no' => $agr->agreement_ref_no ?? ('AGR-' . $agr->id),
                    'vendor_name' => $agr->vendor->name ?? 'N/A',
                    'site_name' => $siteName,
                    'total_months' => $result->totalMonths,
                    'total_npv' => $calculatedNpv ?? 0.00,
                    'total_undiscounted_outflow' => round($result->totalUndiscountedOutflow, 2),
                    'total_gross_rent' => round($result->totalGrossRent, 2),
                    'total_advance_deductions' => round($result->totalAdvanceDeductions, 2),
                    'total_deposit_refunds' => round($result->totalDepositRefunds, 2),
                    'calculated_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($hasPaymentStartCol) {
                    $summaryData['payment_start_date'] = $paymentStartVal;
                    $summaryData['expiry_date'] = $expiryVal;
                }
                if (Schema::hasColumn('npv_agreement_summaries', 'from_date')) {
                    $summaryData['from_date'] = $paymentStartVal;
                }
                if (Schema::hasColumn('npv_agreement_summaries', 'to_date')) {
                    $summaryData['to_date'] = $expiryVal;
                }

                $upsertData[] = $summaryData;

                $rows[] = new NpvSummaryRow(
                    agreementId: $summaryData['agreement_id'],
                    agreementRefNo: $summaryData['agreement_ref_no'],
                    vendorName: $summaryData['vendor_name'],
                    siteName: $summaryData['site_name'],
                    paymentStartDate: $paymentStartVal,
                    expiryDate: $expiryVal,
                    totalMonths: $summaryData['total_months'],
                    totalNPV: $calculatedNpv,
                    totalUndiscountedOutflow: $summaryData['total_undiscounted_outflow'],
                    totalGrossRent: $summaryData['total_gross_rent'],
                    totalAdvanceDeductions: $summaryData['total_advance_deductions'],
                    totalDepositRefunds: $summaryData['total_deposit_refunds'],
                    annualDiscountRate: $hasRate ? $effectiveRate : null,
                    hasInterestRate: $hasRate,
                    fromDate: $paymentStartVal,
                    toDate: $expiryVal
                );
            } catch (\Exception $e) {
                // Continue calculating remaining agreements if 1 fails
            }
        }

        // Bulk upsert into npv_agreement_summaries table
        if (!empty($upsertData)) {
            $updateCols = [
                'agreement_ref_no',
                'vendor_name',
                'site_name',
                'total_months',
                'total_npv',
                'total_undiscounted_outflow',
                'total_gross_rent',
                'total_advance_deductions',
                'total_deposit_refunds',
                'calculated_at',
                'updated_at'
            ];
            if ($hasPaymentStartCol) {
                $updateCols[] = 'payment_start_date';
                $updateCols[] = 'expiry_date';
            }
            if (Schema::hasColumn('npv_agreement_summaries', 'from_date')) {
                $updateCols[] = 'from_date';
            }
            if (Schema::hasColumn('npv_agreement_summaries', 'to_date')) {
                $updateCols[] = 'to_date';
            }

            NpvAgreementSummary::upsert($upsertData, ['agreement_id', 'discount_rate'], $updateCols);
        }

        // Sort: agreements with configured interest rate sorted by Total NPV descending;
        // agreements without rate placed at the end sorted by total gross rent.
        usort($rows, function ($a, $b) {
            if ($a->hasInterestRate && !$b->hasInterestRate) {
                return -1;
            }
            if (!$a->hasInterestRate && $b->hasInterestRate) {
                return 1;
            }
            if ($a->hasInterestRate && $b->hasInterestRate) {
                return ($b->totalNPV ?? 0) <=> ($a->totalNPV ?? 0);
            }
            return ($b->totalGrossRent ?? 0) <=> ($a->totalGrossRent ?? 0);
        });

        return $rows;
    }

    /**
     * Fetch full calculation result for an agreement (for AJAX detail modal).
     */
    public function getDetailBreakdown(int $agreementId, ?float $annualDiscountRate = null): NpvCalculationResult
    {
        $agreement = Agreement::with([
            'vendor',
            'floors.building',
            'rentBases' => fn($q) => $q->orderBy('id', 'desc')->with('components'),
            'rentIncrements',
            'securityDeposits' => fn($q) => $q->orderBy('id', 'desc'),
        ])->findOrFail($agreementId);

        $rate = ($annualDiscountRate !== null && $annualDiscountRate > 0)
            ? $annualDiscountRate
            : ((!is_null($agreement->npv_interest_rate) && (float) $agreement->npv_interest_rate > 0) ? (float) $agreement->npv_interest_rate : null);

        if ($rate === null || $rate <= 0) {
            throw new \InvalidArgumentException("Agreement '{$agreement->agreement_ref_no}' does not have an NPV interest rate configured. Please configure an interest rate first.");
        }

        $baseDate = $agreement->payment_start_date 
            ?? ($agreement->from_date ?? ($agreement->agreement_date ?: now()->startOfMonth()->format('Y-m-d')));

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
    public function ensureTableExists(): void
    {
        if (Schema::hasTable('agreements') && !Schema::hasColumn('agreements', 'npv_interest_rate')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->decimal('npv_interest_rate', 5, 2)->nullable()->after('status');
            });
        }

        if (!Schema::hasTable('npv_agreement_summaries')) {
            Schema::create('npv_agreement_summaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agreement_id');
                $table->decimal('discount_rate', 5, 2)->default(0.00);
                $table->string('agreement_ref_no');
                $table->string('vendor_name')->nullable();
                $table->string('site_name')->nullable();
                $table->string('payment_start_date', 50)->nullable();
                $table->string('expiry_date', 50)->nullable();
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
