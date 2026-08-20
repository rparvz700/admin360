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

        // Default base date to selected agreement's payment_start_date, or start of current month
        $defaultBaseDate = $request->get('base_date') 
            ?: (($selectedAgreement?->payment_start_date ?? $selectedAgreement?->from_date) ?: now()->startOfMonth()->format('Y-m-d'));

        $initialResult = null;

        if ($selectedAgreementId) {
            try {
                $input = new NpvCalculationInput(
                    agreementId: (int) $selectedAgreementId,
                    baseDate: $defaultBaseDate,
                    annualDiscountRate: (float) $request->get('annual_discount_rate', $defaultRate)
                );
                $initialResult = $this->npvService->calculate($input);
            } catch (\Exception $e) {
                // Return view with error message if any issue
            }
        }

        return view('FacilitiesManagement.Npv.index', compact(
            'agreements',
            'defaultRate',
            'vatTax',
            'defaultBaseDate',
            'selectedAgreementId',
            'initialResult'
        ));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'agreement_id' => 'required|exists:agreements,id',
            'base_date' => 'required|date',
            'annual_discount_rate' => 'required|numeric|min:0|max:100',
            'custom_tax_rate' => 'nullable|numeric|min:0|max:100',
            'custom_vat_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $input = new NpvCalculationInput(
                agreementId: (int) $request->input('agreement_id'),
                baseDate: $request->input('base_date'),
                annualDiscountRate: (float) $request->input('annual_discount_rate'),
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
            'annual_discount_rate' => 'required|numeric|min:0|max:100',
        ]);

        $input = new NpvCalculationInput(
            agreementId: (int) $request->input('agreement_id'),
            baseDate: $request->input('base_date'),
            annualDiscountRate: (float) $request->input('annual_discount_rate')
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
            fputcsv($handle, []);

            // Data Table Headers
            fputcsv($handle, [
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
            ]);

            // Data Rows
            foreach ($result->cashFlows as $cf) {
                fputcsv($handle, [
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
                ]);
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
        $defaultRate = FinanceSetting::getValue('npv_annual_discount_rate', 12.16);
        $annualDiscountRate = (float) $request->get('annual_discount_rate', $defaultRate);

        // Fetch summary rows (leveraging fast caching + eager loading)
        $summaryRows = $this->reportService->generateSummaryForAll($annualDiscountRate);

        // Aggregate KPI Metrics
        $totalAgreements = count($summaryRows);
        $portfolioTotalNPV = array_sum(array_column(array_map(fn($r) => $r->toArray(), $summaryRows), 'total_npv'));
        $portfolioTotalOutflow = array_sum(array_column(array_map(fn($r) => $r->toArray(), $summaryRows), 'total_undiscounted_outflow'));
        $portfolioTotalGrossRent = array_sum(array_column(array_map(fn($r) => $r->toArray(), $summaryRows), 'total_gross_rent'));
        $averageNPV = $totalAgreements > 0 ? ($portfolioTotalNPV / $totalAgreements) : 0;

        return view('FacilitiesManagement.Npv.report', compact(
            'summaryRows',
            'defaultRate',
            'annualDiscountRate',
            'totalAgreements',
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
        $defaultRate = FinanceSetting::getValue('npv_annual_discount_rate', 12.16);
        $annualDiscountRate = (float) $request->get('annual_discount_rate', $defaultRate);

        $summaryRows = $this->reportService->generateSummaryForAll($annualDiscountRate);
        $data = array_map(fn($r) => $r->toArray(), $summaryRows);

        return response()->json([
            'success' => true,
            'data' => $data,
            'total_npv' => array_sum(array_column($data, 'total_npv')),
            'total_outflow' => array_sum(array_column($data, 'total_undiscounted_outflow')),
            'count' => count($data),
        ]);
    }

    /**
     * Fetch full calculation breakdown for AJAX detail modal.
     */
    public function agreementDetail(Request $request, int $agreementId)
    {
        $defaultRate = FinanceSetting::getValue('npv_annual_discount_rate', 12.16);
        $annualDiscountRate = (float) $request->get('annual_discount_rate', $defaultRate);

        try {
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
        $defaultRate = FinanceSetting::getValue('npv_annual_discount_rate', 12.16);
        $this->reportService->generateSummaryForAll($defaultRate, forceRefresh: true);

        return back()->with('success', 'NPV Report cache has been successfully cleared and recalculated!');
    }
}
