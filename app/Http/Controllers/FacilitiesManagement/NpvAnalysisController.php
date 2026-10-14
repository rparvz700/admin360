<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\FinanceSetting;
use App\Models\VatTax;
use App\Services\Npv\Dto\NpvCalculationInput;
use App\Services\Npv\NpvCalculationService;
use App\Services\Npv\NpvReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NpvAnalysisController extends Controller
{
    public function __construct(
        private NpvCalculationService $npvService,
        private NpvReportService $reportService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $agreements = Agreement::with(['vendor', 'floors.building'])
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();

        $defaultRate = FinanceSetting::getValue('npv_annual_discount_rate', 12.16);
        $vatTax = VatTax::where('type', 'rent')->where('status', 1)->first();

        $selectedAgreementId = $request->get('agreement_id');
        $selectedAgreement = $selectedAgreementId ? $agreements->firstWhere('id', $selectedAgreementId) : null;

        $hasConfiguredRate = $selectedAgreement 
            && !is_null($selectedAgreement->npv_interest_rate) 
            && (float) $selectedAgreement->npv_interest_rate > 0;

        // Default base date to selected agreement's payment_start_date, or start of current month
        $defaultBaseDate = $request->get('base_date') 
            ?: (($selectedAgreement?->payment_start_date ?? $selectedAgreement?->from_date) ?: now()->startOfMonth()->format('Y-m-d'));

        $initialResult = null;
        $rateNotConfigured = false;
        $currentRate = null;

        if ($selectedAgreement) {
            if ($hasConfiguredRate) {
                $currentRate = (float) $request->get('annual_discount_rate', $selectedAgreement->npv_interest_rate);
                try {
                    $input = new NpvCalculationInput(
                        agreementId: (int) $selectedAgreement->id,
                        baseDate: $defaultBaseDate,
                        annualDiscountRate: $currentRate
                    );
                    $initialResult = $this->npvService->calculate($input);
                } catch (\Exception $e) {
                    // Return view with error message if any issue
                }
            } else {
                // If agreement has no interest rate, do not calculate NPV values on npv index page!
                $rateNotConfigured = true;
                $initialResult = null;
            }
        }

        return view('FacilitiesManagement.Npv.index', compact(
            'agreements',
            'defaultRate',
            'vatTax',
            'defaultBaseDate',
            'selectedAgreementId',
            'selectedAgreement',
            'hasConfiguredRate',
            'currentRate',
            'rateNotConfigured',
            'initialResult'
        ));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'agreement_id' => 'required|exists:agreements,id',
            'base_date' => 'required|date',
            'annual_discount_rate' => 'nullable|numeric|min:0|max:100',
            'custom_tax_rate' => 'nullable|numeric|min:0|max:100',
            'custom_vat_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $agreement = Agreement::findOrFail($request->input('agreement_id'));
        $hasConfiguredRate = !is_null($agreement->npv_interest_rate) && (float) $agreement->npv_interest_rate > 0;

        if (!$hasConfiguredRate && (!$request->filled('annual_discount_rate') || (float) $request->input('annual_discount_rate') <= 0)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agreement does not have an NPV interest rate configured. Please set the interest rate in the Net Present Value (NPV) Valuation Report page.',
                ], 422);
            }

            return redirect()->route('facilities.npv.index', ['agreement_id' => $agreement->id])
                ->with('error', 'Cannot calculate NPV: Agreement "' . $agreement->agreement_ref_no . '" has no interest rate set. Please set the interest rate in the Net Present Value (NPV) Valuation Report page.');
        }

        $rateToUse = $request->filled('annual_discount_rate') && (float) $request->input('annual_discount_rate') > 0
            ? (float) $request->input('annual_discount_rate')
            : (float) $agreement->npv_interest_rate;

        try {
            $input = new NpvCalculationInput(
                agreementId: (int) $agreement->id,
                baseDate: $request->input('base_date'),
                annualDiscountRate: $rateToUse,
                customTaxRate: $request->filled('custom_tax_rate') ? (float) $request->input('custom_tax_rate') : null,
                customVatRate: $request->filled('custom_vat_rate') ? (float) $request->input('custom_vat_rate') : null,
            );

            $result = $this->npvService->calculate($input);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $result->toArray(),
                ]);
            }

            return redirect()->route('facilities.npv.index', [
                'agreement_id' => $input->agreementId,
                'base_date' => $input->baseDate,
                'annual_discount_rate' => $input->annualDiscountRate,
            ]);

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', 'NPV Calculation Error: ' . $e->getMessage());
        }
    }

    public function export(Request $request, string $format = 'csv')
    {
        $request->validate([
            'agreement_id' => 'required|exists:agreements,id',
            'base_date' => 'required|date',
            'annual_discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $agreement = Agreement::findOrFail($request->input('agreement_id'));
        $hasConfiguredRate = !is_null($agreement->npv_interest_rate) && (float) $agreement->npv_interest_rate > 0;

        if (!$hasConfiguredRate && (!$request->filled('annual_discount_rate') || (float) $request->input('annual_discount_rate') <= 0)) {
            return back()->with('error', 'Cannot export NPV calculation: This agreement does not have an NPV interest rate configured.');
        }

        $rateToUse = $request->filled('annual_discount_rate') && (float) $request->input('annual_discount_rate') > 0
            ? (float) $request->input('annual_discount_rate')
            : (float) $agreement->npv_interest_rate;

        $input = new NpvCalculationInput(
            agreementId: (int) $agreement->id,
            baseDate: $request->input('base_date'),
            annualDiscountRate: $rateToUse
        );

        $result = $this->npvService->calculate($input);
        $fileName = 'NPV_Breakdown_' . ($result->agreement->agreement_ref_no ?? 'Lease') . '_' . date('Y-m-d') . '.csv';

        return new StreamedResponse(function () use ($result) {
            $handle = fopen('php://output', 'w');

            // Header info
            fputcsv($handle, ['Net Present Value (NPV) Calculation Report']);
            fputcsv($handle, ['Agreement Reference', $result->agreement->agreement_ref_no]);
            fputcsv($handle, ['Vendor / Landlord', $result->agreement->vendor->name ?? 'N/A']);
            fputcsv($handle, ['Agreement Date', $result->agreement->agreement_date ?? $result->baseDate]);
            fputcsv($handle, ['Payment Start Date', $result->agreement->payment_start_date ?? ($result->agreement->from_date ?? 'N/A')]);
            fputcsv($handle, ['Expiry Date', $result->expiryDate]);
            fputcsv($handle, ['Annual Discount Rate (%)', $result->annualDiscountRate . '%']);
            fputcsv($handle, ['Monthly Compounding Rate (%)', number_format($result->monthlyDiscountRate * 100, 6) . '%']);
            fputcsv($handle, ['Total Duration (Months)', $result->totalMonths]);
            fputcsv($handle, ['TOTAL NPV (Present Value)', number_format($result->totalNPV, 2)]);
            fputcsv($handle, ['Total Undiscounted Net Outflow', number_format($result->totalUndiscountedOutflow, 2)]);
            fputcsv($handle, ['Total Advance Deductions', number_format($result->totalAdvanceDeductions, 2)]);
            fputcsv($handle, ['Total Security Deposit Refunds', number_format($result->totalDepositRefunds, 2)]);
            if ($result->hasIfrs16Schedule) {
                fputcsv($handle, ['IFRS 16 Schedule', 'Applicable (NPV >= 1,000,000 BDT)']);
                fputcsv($handle, ['Opening Lease Obligation', number_format($result->initialLeaseLiability, 2)]);
                fputcsv($handle, ['Lease Payment', number_format($result->totalLeasePayments, 2)]);
                fputcsv($handle, ['Closing Lease Obligation', '0.00']);
                fputcsv($handle, ['Finance Expense', number_format($result->totalFinanceExpense, 2)]);
                fputcsv($handle, ['PPE Opening Balance', number_format($result->initialRouAsset, 2)]);
                fputcsv($handle, ['Depreciation Charged', number_format($result->totalDepreciation, 2)]);
                fputcsv($handle, ['WDV', '0.00']);
            }
            fputcsv($handle, []);

            // Data Table Headers
            $headers = [
                'Period #',
                'Billing Month',
                'Month Label',
                'Office Gross Rent (Tk)',
                'DG Gross Rent (Tk)',
                'Parking Gross Rent (Tk)',
                'Store Gross Rent (Tk)',
                'Total Gross Rent (Tk)',
                'Advance Deduction (-Tk)',
                'Deposit Refund (-Tk)',
                'Net Cash Outflow (Tk)',
                'Discount Factor',
                'Present Value (NPV) (Tk)',
                'Cumulative PV (Tk)',
            ];

            if ($result->hasIfrs16Schedule) {
                $headers = array_merge($headers, [
                    'Opening Lease Obligation (Tk)',
                    'Lease Payment (Tk)',
                    'Closing Lease Obligation (Tk)',
                    'Finance Expense (Tk)',
                    'PPE Opening Balance (Tk)',
                    'Depreciation Charged (Tk)',
                    'WDV (Tk)',
                ]);
            }

            fputcsv($handle, $headers);

            // Data Rows
            foreach ($result->cashFlows as $cf) {
                $row = [
                    $cf->periodIndex,
                    $cf->billingMonth,
                    $cf->monthLabel,
                    number_format($cf->officeGrossRent, 2, '.', ''),
                    number_format($cf->dgGrossRent, 2, '.', ''),
                    number_format($cf->parkingGrossRent, 2, '.', ''),
                    number_format($cf->storeGrossRent, 2, '.', ''),
                    number_format($cf->totalGrossRent, 2, '.', ''),
                    number_format($cf->advanceDeduction, 2, '.', ''),
                    number_format($cf->depositRefund, 2, '.', ''),
                    number_format($cf->netOutflow, 2, '.', ''),
                    number_format($cf->discountFactor, 6, '.', ''),
                    number_format($cf->presentValue, 2, '.', ''),
                    number_format($cf->cumulativePV, 2, '.', ''),
                ];

                if ($result->hasIfrs16Schedule) {
                    $row[] = $cf->openingLeaseObligation !== null ? number_format($cf->openingLeaseObligation, 2, '.', '') : '';
                    $row[] = $cf->leasePayment !== null ? number_format($cf->leasePayment, 2, '.', '') : '';
                    $row[] = $cf->closingLeaseObligation !== null ? number_format($cf->closingLeaseObligation, 2, '.', '') : '';
                    $row[] = $cf->financeExpense !== null ? number_format($cf->financeExpense, 2, '.', '') : '';
                    $row[] = $cf->rouOpeningBalance !== null ? number_format($cf->rouOpeningBalance, 2, '.', '') : '';
                    $row[] = $cf->depreciationCharged !== null ? number_format($cf->depreciationCharged, 2, '.', '') : '';
                    $row[] = $cf->rouWdv !== null ? number_format($cf->rouWdv, 2, '.', '') : '';
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Render the NPV Portfolio Summary Report page.
     */
    public function report(Request $request)
    {
        // Fetch summary rows (each agreement uses its individual npv_interest_rate)
        $summaryRows = $this->reportService->generateSummaryForAll();

        // Aggregate KPI Metrics
        $totalAgreements = count($summaryRows);
        $calculatedRows = array_filter($summaryRows, fn($r) => $r->hasInterestRate && $r->totalNPV !== null);
        $portfolioTotalNPV = array_sum(array_map(fn($r) => $r->totalNPV, $calculatedRows));
        $portfolioTotalOutflow = array_sum(array_map(fn($r) => $r->totalUndiscountedOutflow, $summaryRows));
        $portfolioTotalGrossRent = array_sum(array_map(fn($r) => $r->totalGrossRent, $summaryRows));
        $calculatedCount = count($calculatedRows);
        $averageNPV = $calculatedCount > 0 ? ($portfolioTotalNPV / $calculatedCount) : 0;

        return view('FacilitiesManagement.Npv.report', compact(
            'summaryRows',
            'totalAgreements',
            'calculatedCount',
            'portfolioTotalNPV',
            'portfolioTotalOutflow',
            'portfolioTotalGrossRent',
            'averageNPV'
        ));
    }

    /**
     * JSON data provider for Server-Side or Client-Side DataTables with value-wise sorting.
     */
    public function reportData(Request $request)
    {
        $summaryRows = $this->reportService->generateSummaryForAll();
        $data = array_map(fn($r) => $r->toArray(), $summaryRows);

        $calculatedRows = array_filter($summaryRows, fn($r) => $r->hasInterestRate && $r->totalNPV !== null);
        $portfolioTotalNPV = array_sum(array_map(fn($r) => $r->totalNPV, $calculatedRows));
        $portfolioTotalOutflow = array_sum(array_map(fn($r) => $r->totalUndiscountedOutflow, $summaryRows));

        return response()->json([
            'success' => true,
            'data' => $data,
            'total_npv' => $portfolioTotalNPV,
            'total_outflow' => $portfolioTotalOutflow,
            'count' => count($data),
            'calculated_count' => count($calculatedRows),
        ]);
    }

    /**
     * Fetch full calculation breakdown for AJAX detail modal.
     */
    public function agreementDetail(Request $request, int $agreementId)
    {
        try {
            $annualDiscountRate = $request->filled('annual_discount_rate') ? (float) $request->get('annual_discount_rate') : null;
            $result = $this->reportService->getDetailBreakdown($agreementId, $annualDiscountRate);

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading detail breakdown: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Force recalculation and flush report cache.
     */
    public function refreshReportCache(Request $request)
    {
        $this->reportService->generateSummaryForAll(forceRefresh: true);

        return back()->with('success', 'NPV Valuation Report cache has been successfully cleared and recalculated!');
    }

    /**
     * Update NPV Interest Rate for an individual agreement and recalculate valuation.
     */
    public function updateInterestRate(Request $request, Agreement $agreement)
    {
        $request->validate([
            'interest_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $rate = $request->filled('interest_rate') && (float) $request->input('interest_rate') > 0
            ? round((float) $request->input('interest_rate'), 4)
            : null;

        $agreement->update(['npv_interest_rate' => $rate]);

        // Invalidate cached summary for this agreement
        \App\Models\NpvAgreementSummary::where('agreement_id', $agreement->id)->delete();

        // Recalculate summary row
        $summaryRows = $this->reportService->generateSummaryForAll(forceRefresh: false);
        $updatedRow = collect($summaryRows)->firstWhere('agreementId', $agreement->id);

        // Recompute portfolio totals
        $calculatedRows = array_filter($summaryRows, fn($r) => $r->hasInterestRate && $r->totalNPV !== null);
        $portfolioTotalNPV = array_sum(array_map(fn($r) => $r->totalNPV, $calculatedRows));
        $portfolioTotalOutflow = array_sum(array_map(fn($r) => $r->totalUndiscountedOutflow, $summaryRows));
        $portfolioTotalGrossRent = array_sum(array_map(fn($r) => $r->totalGrossRent, $summaryRows));
        $calculatedCount = count($calculatedRows);
        $averageNPV = $calculatedCount > 0 ? ($portfolioTotalNPV / $calculatedCount) : 0;

        return response()->json([
            'success' => true,
            'message' => $rate !== null 
                ? "NPV Interest Rate updated to {$rate}% for agreement {$agreement->agreement_ref_no}."
                : "NPV Interest Rate cleared for agreement {$agreement->agreement_ref_no}.",
            'data' => [
                'agreement_id' => $agreement->id,
                'agreement_ref_no' => $agreement->agreement_ref_no,
                'has_interest_rate' => $rate !== null,
                'interest_rate' => $rate,
                'interest_rate_formatted' => $rate !== null ? number_format($rate, 2) . '%' : 'Not Set',
                'total_npv' => $updatedRow?->totalNPV,
                'total_npv_formatted' => $updatedRow?->totalNPV !== null ? '৳ ' . number_format($updatedRow->totalNPV, 2) : 'Rate not set',
                'portfolio_total_npv' => '৳ ' . number_format($portfolioTotalNPV, 2),
                'portfolio_total_outflow' => '৳ ' . number_format($portfolioTotalOutflow, 2),
                'average_npv' => '৳ ' . number_format($averageNPV, 2),
                'calculated_count' => $calculatedCount,
                'total_agreements' => count($summaryRows),
            ],
        ]);
    }
}
