<?php

namespace App\Services\Npv;

use App\Models\Agreement;
use App\Models\PropertiesBuilding;
use App\Services\Npv\Dto\MonthlyCashFlow;
use App\Services\Npv\Dto\NpvCalculationInput;
use App\Services\Npv\Dto\NpvCalculationResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NpvConsolidatedScheduleService
{
    private const CACHE_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private NpvCalculationService $npvService
    ) {}

    /**
     * Map of available metrics with user-friendly display labels and formatting hints.
     */
    public const METRICS = [
        'discount_factor' => [
            'label' => 'Discount factor',
            'type' => 'decimal',
            'decimals' => 4,
            'description' => 'Monthly discount multiplier: 1 / (1 + r)^t',
        ],
        'office_gross' => [
            'label' => 'Cash outflow-office',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Gross office space rent',
        ],
        'store_gross' => [
            'label' => 'Cash outflow-store',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Gross store space rent',
        ],
        'dg_gross' => [
            'label' => 'Cash outflow-DG & Battery room',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Gross generator & battery space rent',
        ],
        'present_value' => [
            'label' => 'NPV',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Discounted monthly net cash outflow',
        ],
        'opening_obligation' => [
            'label' => 'Opening lease obligation',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'IFRS 16 beginning lease liability for the month',
        ],
        'lease_payment' => [
            'label' => 'Lease payment',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Principal reduction payment on lease obligation',
        ],
        'closing_obligation' => [
            'label' => 'Closing lease obligation',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'IFRS 16 ending lease liability for the month',
        ],
        'finance_expense' => [
            'label' => 'Finance expense',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Monthly interest expense on lease liability',
        ],
        'rou_opening' => [
            'label' => 'PPE movement: Opening balance',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Right-of-use asset beginning book balance',
        ],
        'depreciation' => [
            'label' => 'Depreciation charged',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Straight-line monthly depreciation expense',
        ],
        'wdv' => [
            'label' => 'WDV',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Right-of-use asset ending net book value',
        ],
        'net_outflow' => [
            'label' => 'Total Cash Outflow (Net Outflow)',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Gross rent minus advance deduction and security deposit refund',
        ],
        'parking_gross' => [
            'label' => 'Cash outflow-Parking',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Gross car parking rent',
        ],
        'total_gross' => [
            'label' => 'Total Gross Rent',
            'type' => 'currency',
            'decimals' => 0,
            'description' => 'Combined gross rent across all components',
        ],
    ];

    /**
     * Get active agreements formatted for selection dropdown.
     */
    public function getAgreementsList(): array
    {
        $agreements = Agreement::with(['vendor', 'floors.building'])
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();

        return $agreements->map(function ($agr) {
            $siteName = $agr->floors->first()?->building?->site_name 
                ?? ($agr->floors->first()?->building?->code ?? 'N/A');
            $vendorName = $agr->vendor->name ?? 'N/A';
            $hasRate = !is_null($agr->npv_interest_rate) && (float) $agr->npv_interest_rate > 0;

            return [
                'id' => $agr->id,
                'agreement_ref_no' => $agr->agreement_ref_no,
                'vendor_name' => $vendorName,
                'site_name' => $siteName,
                'has_rate' => $hasRate,
                'interest_rate' => $hasRate ? (float) $agr->npv_interest_rate : null,
                'payment_start_date' => $agr->payment_start_date ?? ($agr->from_date ?? 'N/A'),
                'expiry_date' => $agr->expiry_date ?? ($agr->to_date ?? 'N/A'),
                'label' => "{$agr->agreement_ref_no} — {$siteName} ({$vendorName})",
            ];
        })->toArray();
    }

    /**
     * Compute full 12-row monthly schedule for a single selected agreement.
     * Matches the top section layout shown in the reference Excel schedule.
     */
    public function getAgreementSchedule(int $agreementId): array
    {
        $agreement = Agreement::with([
            'vendor',
            'floors.building',
            'rentBases' => fn($q) => $q->orderBy('id', 'desc')->with('components'),
            'rentIncrements',
            'securityDeposits' => fn($q) => $q->orderBy('id', 'desc'),
        ])->findOrFail($agreementId);

        $hasRate = !is_null($agreement->npv_interest_rate) && (float) $agreement->npv_interest_rate > 0;
        $rate = $hasRate ? (float) $agreement->npv_interest_rate : 0.0;
        $updatedAt = $agreement->updated_at?->timestamp ?? 0;
        $cacheKey = "npv_full_schedule_{$agreement->id}_{$updatedAt}_{$rate}";

        /** @var NpvCalculationResult $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($agreement, $hasRate, $rate) {
            $baseDate = $agreement->payment_start_date 
                ?? ($agreement->from_date ?? ($agreement->agreement_date ?: now()->startOfMonth()->format('Y-m-d')));

            $input = new NpvCalculationInput(
                agreementId: $agreement->id,
                baseDate: $baseDate,
                annualDiscountRate: $rate
            );

            return $this->npvService->calculateForAgreement($agreement, $input, allowZeroRate: true);
        });

        // Filter cash flows to regular lease months (periodIndex > 0)
        $cashFlows = array_values(array_filter($result->cashFlows, fn($cf) => $cf->periodIndex > 0));

        // Format timeline month columns for this agreement
        $columns = [];
        $hasParkingRent = false;

        foreach ($cashFlows as $index => $cf) {
            // First payment month uses payment start date or 1-M-y; subsequent months use end-of-month date
            $monthStart = Carbon::parse($cf->billingMonth . '-01');
            $label = ($index === 0) 
                ? $monthStart->format('j-M-y') 
                : $monthStart->endOfMonth()->format('j-M-y');

            $columns[] = [
                'period_index' => $cf->periodIndex,
                'billing_month' => $cf->billingMonth,
                'label' => $label,
                'month_name' => $cf->monthLabel,
            ];

            if ($cf->parkingGrossRent > 0) {
                $hasParkingRent = true;
            }
        }

        // Build 12 rows corresponding to reference Excel template
        $rows = [];

        // 1. Discount factor (4 decimals)
        $discountValues = [];
        foreach ($cashFlows as $cf) {
            $discountValues[$cf->billingMonth] = number_format($cf->discountFactor, 4, '.', '');
        }
        $rows[] = [
            'key' => 'discount_factor',
            'label' => 'Discount factor',
            'type' => 'decimal',
            'decimals' => 4,
            'is_header' => false,
            'values' => $discountValues,
            'total' => '-',
        ];

        // 2. Cash outflow-office (round figure, 0 decimals)
        $officeValues = [];
        $officeTotal = 0.0;
        foreach ($cashFlows as $cf) {
            $officeValues[$cf->billingMonth] = round($cf->officeGrossRent);
            $officeTotal += $cf->officeGrossRent;
        }
        $rows[] = [
            'key' => 'office_gross',
            'label' => 'Cash outflow-office',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $officeValues,
            'total' => round($officeTotal),
        ];

        // 3. Cash outflow-store (round figure, 0 decimals)
        $storeValues = [];
        $storeTotal = 0.0;
        foreach ($cashFlows as $cf) {
            $storeValues[$cf->billingMonth] = round($cf->storeGrossRent);
            $storeTotal += $cf->storeGrossRent;
        }
        $rows[] = [
            'key' => 'store_gross',
            'label' => 'Cash outflow-store',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $storeValues,
            'total' => round($storeTotal),
        ];

        // 4. Cash outflow-DG & Battery room (round figure, 0 decimals)
        $dgValues = [];
        $dgTotal = 0.0;
        foreach ($cashFlows as $cf) {
            $dgValues[$cf->billingMonth] = round($cf->dgGrossRent);
            $dgTotal += $cf->dgGrossRent;
        }
        $rows[] = [
            'key' => 'dg_gross',
            'label' => 'Cash outflow-DG & Battery room',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $dgValues,
            'total' => round($dgTotal),
        ];

        // Optional: Cash outflow-Parking (only if present)
        if ($hasParkingRent) {
            $parkingValues = [];
            $parkingTotal = 0.0;
            foreach ($cashFlows as $cf) {
                $parkingValues[$cf->billingMonth] = round($cf->parkingGrossRent);
                $parkingTotal += $cf->parkingGrossRent;
            }
            $rows[] = [
                'key' => 'parking_gross',
                'label' => 'Cash outflow-Parking',
                'type' => 'currency',
                'decimals' => 0,
                'is_header' => false,
                'values' => $parkingValues,
                'total' => round($parkingTotal),
            ];
        }

        // Cash outflow (Net Outflow)
        $netOutflowValues = [];
        $netOutflowTotal = 0.0;
        foreach ($cashFlows as $cf) {
            $netOutflowValues[$cf->billingMonth] = round($cf->netOutflow);
            $netOutflowTotal += $cf->netOutflow;
        }
        $rows[] = [
            'key' => 'net_outflow',
            'label' => 'Total Net Cash Outflow',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => true,
            'values' => $netOutflowValues,
            'total' => round($netOutflowTotal),
        ];

        // 5. NPV (Discounted Present Value)
        $npvValues = [];
        $npvTotal = 0.0;
        foreach ($cashFlows as $cf) {
            $npvValues[$cf->billingMonth] = round($cf->presentValue);
            $npvTotal += $cf->presentValue;
        }
        $rows[] = [
            'key' => 'present_value',
            'label' => 'NPV',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => true,
            'values' => $npvValues,
            'total' => round($npvTotal),
        ];

        // 6. Opening lease obligation
        $openObligationValues = [];
        foreach ($cashFlows as $cf) {
            $openObligationValues[$cf->billingMonth] = $cf->openingLeaseObligation !== null ? round($cf->openingLeaseObligation) : '-';
        }
        $rows[] = [
            'key' => 'opening_obligation',
            'label' => 'Opening lease obligation',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $openObligationValues,
            'total' => $result->initialLeaseLiability !== null ? round($result->initialLeaseLiability) : '-',
        ];

        // 7. Lease payment
        $leasePaymentValues = [];
        $sumLeasePayments = 0.0;
        foreach ($cashFlows as $cf) {
            $val = $cf->leasePayment !== null ? round($cf->leasePayment) : null;
            $leasePaymentValues[$cf->billingMonth] = $val !== null ? $val : '-';
            if ($val !== null) $sumLeasePayments += $val;
        }
        $rows[] = [
            'key' => 'lease_payment',
            'label' => 'Lease payment',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $leasePaymentValues,
            'total' => $result->hasIfrs16Schedule ? round($sumLeasePayments) : '-',
        ];

        // 8. Closing lease obligation
        $closingObligationValues = [];
        foreach ($cashFlows as $cf) {
            $closingObligationValues[$cf->billingMonth] = $cf->closingLeaseObligation !== null ? round($cf->closingLeaseObligation) : '-';
        }
        $rows[] = [
            'key' => 'closing_obligation',
            'label' => 'Closing lease obligation',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $closingObligationValues,
            'total' => $result->hasIfrs16Schedule ? 0 : '-',
        ];

        // 9. Finance expense
        $financeValues = [];
        $sumFinance = 0.0;
        foreach ($cashFlows as $cf) {
            $val = $cf->financeExpense !== null ? round($cf->financeExpense) : null;
            $financeValues[$cf->billingMonth] = $val !== null ? $val : '-';
            if ($val !== null) $sumFinance += $val;
        }
        $rows[] = [
            'key' => 'finance_expense',
            'label' => 'Finance expense',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $financeValues,
            'total' => $result->totalFinanceExpense !== null ? round($result->totalFinanceExpense) : '-',
        ];

        // 10. PPE movement: Opening balance
        $rouOpenValues = [];
        foreach ($cashFlows as $cf) {
            $rouOpenValues[$cf->billingMonth] = $cf->rouOpeningBalance !== null ? round($cf->rouOpeningBalance) : '-';
        }
        $rows[] = [
            'key' => 'rou_opening',
            'label' => 'PPE movement: Opening balance',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $rouOpenValues,
            'total' => $result->initialRouAsset !== null ? round($result->initialRouAsset) : '-',
        ];

        // 11. Depreciation charged
        $deprecValues = [];
        $sumDeprec = 0.0;
        foreach ($cashFlows as $cf) {
            $val = $cf->depreciationCharged !== null ? round($cf->depreciationCharged) : null;
            $deprecValues[$cf->billingMonth] = $val !== null ? $val : '-';
            if ($val !== null) $sumDeprec += $val;
        }
        $rows[] = [
            'key' => 'depreciation',
            'label' => 'Depreciation charged',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $deprecValues,
            'total' => $result->totalDepreciation !== null ? round($result->totalDepreciation) : '-',
        ];

        // 12. WDV
        $wdvValues = [];
        foreach ($cashFlows as $cf) {
            $wdvValues[$cf->billingMonth] = $cf->rouWdv !== null ? round($cf->rouWdv) : '-';
        }
        $rows[] = [
            'key' => 'wdv',
            'label' => 'WDV',
            'type' => 'currency',
            'decimals' => 0,
            'is_header' => false,
            'values' => $wdvValues,
            'total' => $result->hasIfrs16Schedule ? 0 : '-',
        ];

        // Metadata Profile
        $firstFloor = $agreement->floors->first();
        $siteName = $firstFloor?->building?->site_name ?? ($firstFloor?->building?->code ?? 'N/A');
        $premisesTypes = $agreement->floors->pluck('premises_type')->filter()->unique()->implode(', ') ?: 'Office Space';
        $floorLabels = $agreement->floors->pluck('floor_label')->filter()->implode(', ') ?: 'N/A';
        $totalSft = (float) $agreement->floors->sum('floor_area_sft');
        if ($totalSft <= 0 && $agreement->rentBases->isNotEmpty()) {
            $totalSft = (float) $agreement->rentBases->first()?->components->sum('area_sft');
        }

        return [
            'agreement' => [
                'id' => $agreement->id,
                'agreement_ref_no' => $agreement->agreement_ref_no,
                'vendor_name' => $agreement->vendor->name ?? 'N/A',
                'site_name' => $siteName,
                'premises_type' => $premisesTypes,
                'floor_no' => $floorLabels,
                'space_sft' => round($totalSft),
                'payment_start_date' => $agreement->payment_start_date ?? ($agreement->from_date ?? 'N/A'),
                'expiry_date' => $agreement->expiry_date ?? ($agreement->to_date ?? 'N/A'),
                'total_months' => $result->totalMonths,
                'has_rate' => $hasRate,
                'interest_rate' => $hasRate ? (float) $agreement->npv_interest_rate : null,
                'total_npv' => round($result->totalNPV),
                'total_outflow' => round($result->totalUndiscountedOutflow),
                'has_ifrs16' => $result->hasIfrs16Schedule,
            ],
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    /**
     * Build consolidated multi-agreement matrix along a unified chronological calendar timeline.
     * Aligns agreements with differing start and expiry dates along calendar months YYYY-MM.
     * Excludes total row as explicitly requested.
     *
     * @param string $metric One of self::METRICS keys
     * @param string|null $selectedYear Specific year 'YYYY' or 'all'
     * @param int|null $buildingId Filter by building/site
     * @param string|null $search Search keyword for ref no or vendor
     * @return array
     */
    public function getConsolidatedMatrix(
        string $metric = 'net_outflow',
        ?string $selectedYear = 'all',
        ?int $buildingId = null,
        ?string $search = null
    ): array {
        if (!array_key_exists($metric, self::METRICS)) {
            $metric = 'net_outflow';
        }

        $metricConfig = self::METRICS[$metric];

        // 1. Query active agreements
        $query = Agreement::with([
            'vendor',
            'floors.building',
            'rentBases' => fn($q) => $q->orderBy('id', 'desc')->with('components'),
            'rentIncrements',
            'securityDeposits' => fn($q) => $q->orderBy('id', 'desc'),
        ])
        ->where('status', 1)
        ->orderBy('id', 'desc');

        if ($buildingId) {
            $query->whereHas('floors', fn($q) => $q->where('building_id', $buildingId));
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('agreement_ref_no', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($vq) => $vq->where('name', 'like', "%{$search}%"));
            });
        }

        $agreements = $query->get();

        // 2. Pre-calculate or fetch cached cash flows for each agreement
        $agreementCashFlows = [];
        $allBillingMonths = [];

        foreach ($agreements as $agr) {
            $hasRate = !is_null($agr->npv_interest_rate) && (float) $agr->npv_interest_rate > 0;
            $rate = $hasRate ? (float) $agr->npv_interest_rate : 0.0;
            $updatedAt = $agr->updated_at?->timestamp ?? 0;
            $cacheKey = "npv_full_schedule_{$agr->id}_{$updatedAt}_{$rate}";

            /** @var NpvCalculationResult $calcResult */
            $calcResult = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($agr, $rate) {
                $baseDate = $agr->payment_start_date 
                    ?? ($agr->from_date ?? ($agr->agreement_date ?: now()->startOfMonth()->format('Y-m-d')));

                $input = new NpvCalculationInput(
                    agreementId: $agr->id,
                    baseDate: $baseDate,
                    annualDiscountRate: $rate
                );

                return $this->npvService->calculateForAgreement($agr, $input, allowZeroRate: true);
            });

            // Map monthly cash flows by billingMonth (periodIndex > 0)
            $flowMap = [];
            foreach ($calcResult->cashFlows as $cf) {
                if ($cf->periodIndex > 0) {
                    $flowMap[$cf->billingMonth] = $cf;
                    $allBillingMonths[$cf->billingMonth] = true;
                }
            }

            $agreementCashFlows[$agr->id] = [
                'agreement' => $agr,
                'result' => $calcResult,
                'flowMap' => $flowMap,
            ];
        }

        // 3. Determine sorted global chronological timeline
        ksort($allBillingMonths);
        $timelineMonths = array_keys($allBillingMonths);

        // Derive available years
        $allYears = [];
        foreach ($timelineMonths as $m) {
            $year = substr($m, 0, 4);
            $allYears[$year] = true;
        }
        $availableYears = array_keys($allYears);
        sort($availableYears);

        // 4. Apply year filter if specified
        $filteredMonths = $timelineMonths;
        if (!empty($selectedYear) && $selectedYear !== 'all') {
            $filteredMonths = array_values(array_filter($timelineMonths, fn($m) => str_starts_with($m, $selectedYear . '-')));
        }

        // 5. Build timeline column headers
        $columns = [];
        foreach ($filteredMonths as $m) {
            $monthStart = Carbon::parse($m . '-01');
            $columns[] = [
                'key' => $m,
                'label' => $monthStart->endOfMonth()->format('j-M-y'),
                'month_name' => $monthStart->format('M Y'),
                'year' => $monthStart->format('Y'),
            ];
        }

        // 6. Build agreement matrix rows
        $rows = [];
        foreach ($agreementCashFlows as $agrId => $item) {
            /** @var Agreement $agr */
            $agr = $item['agreement'];
            /** @var array<string, MonthlyCashFlow> $flowMap */
            $flowMap = $item['flowMap'];

            $firstFloor = $agr->floors->first();
            $siteName = $firstFloor?->building?->site_name ?? ($firstFloor?->building?->code ?? 'N/A');
            $spaceType = $agr->floors->pluck('premises_type')->filter()->unique()->implode(', ') ?: 'Office Space';
            $floorNo = $agr->floors->pluck('floor_label')->filter()->implode(', ') ?: 'N/A';
            
            $spaceSft = (float) $agr->floors->sum('floor_area_sft');
            if ($spaceSft <= 0 && $agr->rentBases->isNotEmpty()) {
                $spaceSft = (float) $agr->rentBases->first()?->components->sum('area_sft');
            }

            $hasRate = !is_null($agr->npv_interest_rate) && (float) $agr->npv_interest_rate > 0;

            $monthValues = [];
            $rowSum = 0.0;
            $hasAnyValueInFilter = false;

            foreach ($filteredMonths as $m) {
                if (isset($flowMap[$m])) {
                    $cf = $flowMap[$m];
                    $val = $this->extractMetricValue($cf, $metric);
                    if ($val !== null) {
                        $monthValues[$m] = $metricConfig['decimals'] === 4
                            ? (float) number_format($val, 4, '.', '')
                            : (float) round($val);

                        if ($metric !== 'discount_factor') {
                            $rowSum += $val;
                        }
                        $hasAnyValueInFilter = true;
                    } else {
                        $monthValues[$m] = null;
                    }
                } else {
                    $monthValues[$m] = null; // Blank cell (rendered as '-')
                }
            }

            $rows[] = [
                'id' => $agr->id,
                'agreement_code' => $agr->agreement_ref_no,
                'land_owner' => $agr->vendor->name ?? 'N/A',
                'location' => $siteName,
                'space_type' => $spaceType,
                'floor_no' => $floorNo,
                'space_sft' => round($spaceSft),
                'has_rate' => $hasRate,
                'interest_rate' => $hasRate ? (float) $agr->npv_interest_rate : null,
                'values' => $monthValues,
                'row_total' => ($metric !== 'discount_factor' && $hasAnyValueInFilter) ? round($rowSum) : '-',
            ];
        }

        // Return matrix payload without any total row!
        return [
            'metric' => $metric,
            'metric_label' => $metricConfig['label'],
            'metric_type' => $metricConfig['type'],
            'decimals' => $metricConfig['decimals'],
            'available_years' => $availableYears,
            'selected_year' => $selectedYear ?? 'all',
            'columns' => $columns,
            'rows' => $rows,
            'total_agreements' => count($rows),
        ];
    }

    /**
     * Extract specific metric value from MonthlyCashFlow DTO.
     */
    private function extractMetricValue(MonthlyCashFlow $cf, string $metric): ?float
    {
        return match ($metric) {
            'net_outflow' => $cf->netOutflow,
            'present_value' => $cf->presentValue,
            'office_gross' => $cf->officeGrossRent,
            'store_gross' => $cf->storeGrossRent,
            'dg_gross' => $cf->dgGrossRent,
            'parking_gross' => $cf->parkingGrossRent,
            'total_gross' => $cf->totalGrossRent,
            'opening_obligation' => $cf->openingLeaseObligation,
            'lease_payment' => $cf->leasePayment,
            'closing_obligation' => $cf->closingLeaseObligation,
            'finance_expense' => $cf->financeExpense,
            'rou_opening' => $cf->rouOpeningBalance,
            'depreciation' => $cf->depreciationCharged,
            'wdv' => $cf->rouWdv,
            'discount_factor' => $cf->discountFactor,
            default => $cf->netOutflow,
        };
    }

    /**
     * Export consolidated matrix to CSV stream without a total row.
     */
    public function exportConsolidatedMatrix(
        string $metric = 'net_outflow',
        ?string $selectedYear = 'all',
        ?int $buildingId = null,
        ?string $search = null
    ): StreamedResponse {
        $data = $this->getConsolidatedMatrix($metric, $selectedYear, $buildingId, $search);

        $fileName = 'NPV_Consolidated_Summary_' . strtoupper($metric) . '_' . ($selectedYear ?: 'ALL') . '_' . now()->format('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Metadata info rows
            fputcsv($handle, ['Net Present Value (NPV) Consolidated Schedule Summary']);
            fputcsv($handle, ['Metric', $data['metric_label']]);
            fputcsv($handle, ['Timeline Horizon', $data['selected_year'] === 'all' ? 'All Active Lease Years' : "Year {$data['selected_year']}"]);
            fputcsv($handle, ['Generated At', now()->format('d M Y, h:i A')]);
            fputcsv($handle, []);

            // Header columns
            $headers = [
                'Agreement Code',
                'Land Owner Name',
                'Location',
                'Space Type',
                'Floor No.',
                'Space sft',
            ];

            foreach ($data['columns'] as $col) {
                $headers[] = $col['label'];
            }

            fputcsv($handle, $headers);

            // Data rows (without any bottom total row)
            foreach ($data['rows'] as $row) {
                $csvRow = [
                    $row['agreement_code'],
                    $row['land_owner'],
                    $row['location'],
                    $row['space_type'],
                    $row['floor_no'],
                    number_format($row['space_sft'], 0, '.', ''),
                ];

                foreach ($data['columns'] as $col) {
                    $monthKey = $col['key'];
                    $val = $row['values'][$monthKey] ?? null;
                    if ($val === null) {
                        $csvRow[] = '-';
                    } elseif ($data['decimals'] === 4) {
                        $csvRow[] = number_format($val, 4, '.', '');
                    } else {
                        $csvRow[] = number_format($val, 0, '.', '');
                    }
                }

                fputcsv($handle, $csvRow);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
