<?php

namespace App\Http\Controllers\InvoiceManagement;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VehicleInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:vehicle-invoice-management|invoice-management|create-vehicle-invoice|create-invoice|edit-vehicle-invoice|edit-invoice|delete-vehicle-invoice|delete-invoice', ['only' => ['index', 'show', 'recordPayment', 'download']]);
        $this->middleware('permission:create-vehicle-invoice|create-invoice', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-vehicle-invoice|edit-invoice', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-vehicle-invoice|delete-invoice', ['only' => ['destroy']]);
    }

    /**
     * Scope query to Vehicle Maintenance Invoices only
     */
    private function vehicleInvoiceQuery()
    {
        return Invoice::has('maintenances');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $invoices = $this->vehicleInvoiceQuery()->with([
                'vendor',
                'maintenances.vehicle'
            ])->orderByDesc('invoice_date');

            if ($request->filled('payment_status') && $request->payment_status !== 'all') {
                $invoices->where('payment_status', $request->payment_status);
            }

            return DataTables::of($invoices)
                ->addColumn('item_details', function ($invoice) {
                    return $invoice->invoice_item_html;
                })
                ->filterColumn('item_details', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->whereHas('maintenances', function ($q2) use ($lower) {
                        $q2->whereRaw("LOWER(service_description) LIKE ?", ["%{$lower}%"])
                           ->orWhereHas('vehicle', function ($q3) use ($lower) {
                               $q3->whereRaw("LOWER(registration_number) LIKE ?", ["%{$lower}%"]);
                           });
                    })
                    ->orWhereRaw("LOWER(remarks) LIKE ?", ["%{$lower}%"]);
                })
                ->filterColumn('invoice_number', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->whereRaw("LOWER(invoice_number) LIKE ?", ["%{$lower}%"]);
                })
                ->editColumn('vendor', function ($invoice) {
                    return $invoice->vendor->name ?? 'N/A';
                })
                ->filterColumn('vendor', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->whereHas('vendor', function ($q) use ($lower) {
                        $q->whereRaw("LOWER(name) LIKE ?", ["%{$lower}%"])
                          ->orWhereRaw("LOWER(vendor_code) LIKE ?", ["%{$lower}%"]);
                    });
                })
                ->editColumn('total_amount', function ($invoice) {
                    return '৳ ' . number_format($invoice->total_amount, 2);
                })
                ->editColumn('paid_amount', function ($invoice) {
                    return '৳ ' . number_format($invoice->paid_amount, 2);
                })
                ->addColumn('outstanding', function ($invoice) {
                    return '৳ ' . number_format($invoice->getOutstandingAmount(), 2);
                })
                ->editColumn('payment_status', function ($invoice) {
                    $badge = $invoice->getPaymentStatusBadge();
                    $label = $invoice->getPaymentStatusLabel();
                    return '<span class="badge bg-' . $badge . '">' . $label . '</span>';
                })
                ->addColumn('actions', function ($invoice) {
                    $showUrl  = route('invoices.vehicle.show', $invoice->id);
                    $printUrl = route('invoices.vehicle.print', $invoice->id);

                    $html = '<div class="dropdown d-inline-block">';
                    $html .= '<button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="vehActions' . $invoice->id . '" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                    $html .= '<div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="vehActions' . $invoice->id . '">';

                    $html .= '<a class="dropdown-item py-1" href="' . $showUrl . '"><i class="fa fa-eye text-info me-2"></i> View Details</a>';
                    $html .= '<a class="dropdown-item py-1" href="' . $printUrl . '" target="_blank"><i class="fa fa-print text-secondary me-2"></i> Print Invoice</a>';

                    if (auth()->user()->can('edit-vehicle-invoice') || auth()->user()->can('edit-invoice')) {
                        $html .= '<a class="dropdown-item py-1" href="' . route('invoices.vehicle.edit', $invoice->id) . '"><i class="fa fa-edit text-warning me-2"></i> Edit Invoice</a>';
                    }

                    if ($invoice->payment_status !== 'paid') {
                        $html .= '<a class="dropdown-item py-1 btn-record-payment text-success" href="javascript:void(0)" data-id="' . $invoice->id . '" data-number="' . e($invoice->invoice_number) . '" data-outstanding="' . number_format($invoice->getOutstandingAmount(), 2) . '"><i class="fa fa-credit-card me-2"></i> Record Payment</a>';
                    }

                    if (auth()->user()->can('delete-vehicle-invoice') || auth()->user()->can('delete-invoice')) {
                        $html .= '<div class="dropdown-divider"></div>';
                        $html .= '<form action="' . route('invoices.vehicle.destroy', $invoice->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this vehicle maintenance invoice?\');">';
                        $html .= csrf_field() . method_field('DELETE');
                        $html .= '<button type="submit" class="dropdown-item py-1 text-danger"><i class="fa fa-trash me-2"></i> Delete Invoice</button>';
                        $html .= '</form>';
                    }

                    $html .= '</div></div>';
                    return $html;
                })
                ->rawColumns(['item_details', 'payment_status', 'actions'])
                ->make(true);
        }

        return view('InvoiceManagement.Vehicle.index');
    }

    public function show($id)
    {
        $invoice = $this->vehicleInvoiceQuery()->with([
            'vendor',
            'maintenances.vehicle'
        ])->findOrFail($id);

        return view('InvoiceManagement.Vehicle.show', compact('invoice'));
    }

    public function printInvoice($id)
    {
        $invoice = $this->vehicleInvoiceQuery()->with([
            'vendor',
            'maintenances.vehicle'
        ])->findOrFail($id);

        return view('InvoiceManagement.Vehicle.print', compact('invoice'));
    }

    public function recordPayment(Request $request, $id)
    {
        $invoice = $this->vehicleInvoiceQuery()->findOrFail($id);

        $request->validate([
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
        ]);

        $newPaidAmount = $invoice->paid_amount + $request->paid_amount;
        $status = 'partial';
        if ($newPaidAmount >= $invoice->total_amount) {
            $status = 'paid';
            $newPaidAmount = $invoice->total_amount;
        }

        $invoice->update([
            'paid_amount'    => $newPaidAmount,
            'payment_status' => $status,
            'payment_date'   => $request->payment_date,
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->back()->with('success', 'Payment recorded successfully for Vehicle Invoice #' . $invoice->invoice_number);
    }

    public function destroy($id)
    {
        $invoice = $this->vehicleInvoiceQuery()->findOrFail($id);
        $invoice->delete();

        return redirect()->route('invoices.vehicle.index')->with('success', 'Vehicle Invoice deleted successfully.');
    }
}
