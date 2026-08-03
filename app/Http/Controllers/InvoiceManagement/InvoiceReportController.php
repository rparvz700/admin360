<?php

namespace App\Http\Controllers\InvoiceManagement;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:invoice-management|create-invoice|edit-invoice|delete-invoice');
    }

    public function index(Request $request)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        $query = Invoice::with([
            'vendor',
            'maintenances.vehicle',
            'rentBases.agreement.floors.building',
            'rentBasePivot.agreement.floors.building'
        ]);

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        if ($request->filled('billing_month')) {
            $month = $request->billing_month;
            $parts = explode('-', $month);
            $year  = $parts[0] ?? null;
            $m     = $parts[1] ?? null;

            $query->where(function ($q) use ($month, $year, $m) {
                $q->where('billing_month', $month)
                  ->orWhereHas('rentBasePivot', function ($q2) use ($month) {
                      $q2->where('rent_invoices.billing_month', $month);
                  });
                if ($year && $m) {
                    $q->orWhere(function ($q3) use ($year, $m) {
                        $q3->whereYear('invoice_date', $year)
                           ->whereMonth('invoice_date', $m);
                    });
                }
            });
        }
        if ($request->filled('invoice_type') && $request->invoice_type !== 'all') {
            $type = $request->invoice_type;
            if ($type === 'rent') {
                $query->where(function ($q) {
                    $q->has('rentBases')->orWhereHas('rentBasePivot');
                });
            } elseif ($type === 'maintenance') {
                $query->has('maintenances');
            } elseif ($type === 'general') {
                $query->doesntHave('rentBases')->doesntHave('rentBasePivot')->doesntHave('maintenances');
            }
        }
        if ($request->filled('vendor_id') && $request->vendor_id !== 'all') {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Clone query for totals
        $summaryQuery = clone $query;
        $summary = $summaryQuery->selectRaw("
            COUNT(id) as total_count,
            COALESCE(SUM(subtotal), 0) as subtotal_sum,
            COALESCE(SUM(tax_amount), 0) as tax_sum,
            COALESCE(SUM(discount_amount), 0) as discount_sum,
            COALESCE(SUM(total_amount), 0) as total_sum,
            COALESCE(SUM(paid_amount), 0) as paid_sum,
            COALESCE(SUM(total_amount - paid_amount), 0) as outstanding_sum
        ")->first();

        // Vendor Aging Analysis (Database-agnostic in PHP using Carbon)
        $today = Carbon::today();
        $unpaidInvoices = (clone $query)
            ->whereRaw("(total_amount - paid_amount) > 0")
            ->get(['vendor_id', 'total_amount', 'paid_amount', 'due_date']);

        $vendorAgingMap = [];
        foreach ($unpaidInvoices as $inv) {
            $vId = $inv->vendor_id;
            if (!$vId) continue;

            if (!isset($vendorAgingMap[$vId])) {
                $vendorAgingMap[$vId] = [
                    'vendor_id' => $vId,
                    'total_outstanding' => 0,
                    'current_amount' => 0,
                    'days_1_30' => 0,
                    'days_31_60' => 0,
                    'days_61_90' => 0,
                    'days_over_90' => 0,
                ];
            }

            $outstanding = (float) ($inv->total_amount - $inv->paid_amount);
            $vendorAgingMap[$vId]['total_outstanding'] += $outstanding;

            if (!$inv->due_date || $inv->due_date->greaterThanOrEqualTo($today)) {
                $vendorAgingMap[$vId]['current_amount'] += $outstanding;
            } else {
                $diffDays = (int) $inv->due_date->diffInDays($today);
                if ($diffDays <= 30) {
                    $vendorAgingMap[$vId]['days_1_30'] += $outstanding;
                } elseif ($diffDays <= 60) {
                    $vendorAgingMap[$vId]['days_31_60'] += $outstanding;
                } elseif ($diffDays <= 90) {
                    $vendorAgingMap[$vId]['days_61_90'] += $outstanding;
                } else {
                    $vendorAgingMap[$vId]['days_over_90'] += $outstanding;
                }
            }
        }

        $vendorIds = array_keys($vendorAgingMap);
        $vendorModels = Vendor::whereIn('id', $vendorIds)->get()->keyBy('id');

        $vendorAging = collect($vendorAgingMap)->map(function ($item) use ($vendorModels) {
            $obj = (object) $item;
            $obj->vendor = $vendorModels->get($item['vendor_id']);
            return $obj;
        });

        $invoices = $query->orderByDesc('invoice_date')->paginate(25)->withQueryString();

        return view('InvoiceManagement.reports', compact('invoices', 'vendors', 'summary', 'vendorAging'));
    }

    public function export(Request $request)
    {
        $query = Invoice::with(['vendor', 'rentBases.agreement', 'maintenances.vehicle']);

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        if ($request->filled('billing_month')) {
            $month = $request->billing_month;
            $parts = explode('-', $month);
            $year  = $parts[0] ?? null;
            $m     = $parts[1] ?? null;

            $query->where(function ($q) use ($month, $year, $m) {
                $q->where('billing_month', $month)
                  ->orWhereHas('rentBasePivot', function ($q2) use ($month) {
                      $q2->where('rent_invoices.billing_month', $month);
                  });
                if ($year && $m) {
                    $q->orWhere(function ($q3) use ($year, $m) {
                        $q3->whereYear('invoice_date', $year)
                           ->whereMonth('invoice_date', $m);
                    });
                }
            });
        }
        if ($request->filled('invoice_type') && $request->invoice_type !== 'all') {
            $type = $request->invoice_type;
            if ($type === 'rent') {
                $query->where(function ($q) {
                    $q->has('rentBases')->orWhereHas('rentBasePivot');
                });
            } elseif ($type === 'maintenance') {
                $query->has('maintenances');
            } elseif ($type === 'general') {
                $query->doesntHave('rentBases')->doesntHave('rentBasePivot')->doesntHave('maintenances');
            }
        }
        if ($request->filled('vendor_id') && $request->vendor_id !== 'all') {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        $invoices = $query->orderByDesc('invoice_date')->get();

        $fileName = 'Invoice_Report_' . date('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, [
                'Invoice #',
                'Invoice Type',
                'Item / Description',
                'Vendor Name',
                'Vendor Code',
                'Invoice Date',
                'Due Date',
                'Subtotal (BDT)',
                'Tax Amount (BDT)',
                'Discount (BDT)',
                'Total Amount (BDT)',
                'Paid Amount (BDT)',
                'Outstanding (BDT)',
                'Payment Status',
                'Billing Month',
                'Remarks'
            ]);

            foreach ($invoices as $inv) {
                fputcsv($file, [
                    $inv->invoice_number,
                    $inv->invoice_type_label,
                    strip_tags(str_replace(['<br>', '</div>'], [' ', ' '], $inv->invoice_item_html)),
                    $inv->vendor->name ?? 'N/A',
                    $inv->vendor->vendor_code ?? 'N/A',
                    $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : '',
                    $inv->due_date ? $inv->due_date->format('Y-m-d') : '',
                    $inv->subtotal,
                    $inv->tax_amount,
                    $inv->discount_amount,
                    $inv->total_amount,
                    $inv->paid_amount,
                    $inv->getOutstandingAmount(),
                    ucfirst($inv->payment_status),
                    $inv->billing_month ?? '',
                    $inv->remarks ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
