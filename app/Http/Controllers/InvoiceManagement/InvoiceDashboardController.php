<?php

namespace App\Http\Controllers\InvoiceManagement;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:invoice-management|create-invoice|edit-invoice|delete-invoice');
    }

    public function index(Request $request)
    {
        $today = now()->toDateString();

        // 1. KPI Aggregations (Database-agnostic using bound variables)
        $kpiData = Invoice::selectRaw("
            COUNT(id) as total_count,
            COALESCE(SUM(total_amount), 0) as total_billed,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(total_amount - paid_amount), 0) as total_outstanding,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END), 0) as paid_count,
            COALESCE(SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END), 0) as pending_count,
            COALESCE(SUM(CASE WHEN payment_status = 'partial' THEN 1 ELSE 0 END), 0) as partial_count,
            COALESCE(SUM(CASE WHEN payment_status = 'overdue' OR (payment_status != 'paid' AND due_date < ?) THEN 1 ELSE 0 END), 0) as overdue_count,
            COALESCE(SUM(CASE WHEN payment_status = 'overdue' OR (payment_status != 'paid' AND due_date < ?) THEN (total_amount - paid_amount) ELSE 0 END), 0) as overdue_amount
        ", [$today, $today])->first();

        // 2. Breakdown by Category (Rent vs Maintenance vs General)
        $rentInvoiceIds = DB::table('rent_base')->whereNotNull('invoice_id')->pluck('invoice_id')
            ->merge(DB::table('rent_invoices')->pluck('invoice_id'))->unique();
        $maintInvoiceIds = DB::table('vehicle_maintenances')->whereNotNull('invoice_id')->pluck('invoice_id')->unique();

        $rentBilled = Invoice::whereIn('id', $rentInvoiceIds)->sum('total_amount');
        $maintBilled = Invoice::whereIn('id', $maintInvoiceIds)->whereNotIn('id', $rentInvoiceIds)->sum('total_amount');
        $generalBilled = Invoice::whereNotIn('id', $rentInvoiceIds)->whereNotIn('id', $maintInvoiceIds)->sum('total_amount');

        // 3. Last 12 Months Financial Trend (Database-agnostic PHP collection grouping)
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $rawInvoices = Invoice::where('invoice_date', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->get(['invoice_date', 'total_amount', 'paid_amount']);

        $monthlyBilled = [];
        $monthlyPaid   = [];

        foreach ($rawInvoices as $inv) {
            if ($inv->invoice_date) {
                $mKey = $inv->invoice_date->format('Y-m');
                $monthlyBilled[$mKey] = ($monthlyBilled[$mKey] ?? 0) + (float) $inv->total_amount;
                $monthlyPaid[$mKey]   = ($monthlyPaid[$mKey] ?? 0)   + (float) $inv->paid_amount;
            }
        }

        $trendLabels = [];
        $trendBilledData = [];
        $trendPaidData = [];

        foreach ($months as $m) {
            $trendLabels[] = Carbon::parse($m . '-01')->format('M Y');
            $trendBilledData[] = round((float) ($monthlyBilled[$m] ?? 0), 2);
            $trendPaidData[]   = round((float) ($monthlyPaid[$m]   ?? 0), 2);
        }

        // 4. Status Distribution Percentages
        $statusCounts = [
            'Paid'    => (int) $kpiData->paid_count,
            'Partial' => (int) $kpiData->partial_count,
            'Pending' => (int) $kpiData->pending_count,
            'Overdue' => (int) $kpiData->overdue_count,
        ];

        // 5. Top Vendors by Billed Value
        $topVendors = Vendor::select('vendors.id', 'vendors.name', 'vendors.vendor_code')
            ->selectRaw('COUNT(invoices.id) as invoice_count, SUM(invoices.total_amount) as total_billed, SUM(invoices.paid_amount) as total_paid')
            ->join('invoices', 'vendors.id', '=', 'invoices.vendor_id')
            ->whereNull('invoices.deleted_at')
            ->groupBy('vendors.id', 'vendors.name', 'vendors.vendor_code')
            ->orderByDesc('total_billed')
            ->take(5)
            ->get();

        // 6. Actionable Urgent Overdue Invoices
        $recentOverdue = Invoice::with(['vendor', 'rentBases.agreement', 'maintenances.vehicle'])
            ->where(function ($q) use ($today) {
                $q->where('payment_status', 'overdue')
                  ->orWhere(function ($q2) use ($today) {
                      $q2->where('payment_status', '!=', 'paid')
                         ->where('due_date', '<', $today);
                  });
            })
            ->orderBy('due_date', 'asc')
            ->take(6)
            ->get();

        return view('InvoiceManagement.dashboard', compact(
            'kpiData',
            'rentBilled',
            'maintBilled',
            'generalBilled',
            'trendLabels',
            'trendBilledData',
            'trendPaidData',
            'statusCounts',
            'topVendors',
            'recentOverdue'
        ));
    }
}
