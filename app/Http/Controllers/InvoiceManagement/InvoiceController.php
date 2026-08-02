<?php

namespace App\Http\Controllers\InvoiceManagement;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:invoice-management|create-invoice|edit-invoice|delete-invoice', ['only' => ['index', 'show', 'recordPayment', 'download']]);
        $this->middleware('permission:create-invoice', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-invoice', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-invoice', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $invoices = Invoice::with(['vendor', 'rentBases', 'maintenances'])->orderByDesc('invoice_date');

            if ($request->filled('invoice_type')) {
                $type = $request->invoice_type;
                if ($type === 'rent') {
                    $invoices->has('rentBases');
                } elseif ($type === 'maintenance') {
                    $invoices->has('maintenances');
                } elseif ($type === 'general') {
                    $invoices->doesntHave('rentBases')->doesntHave('maintenances');
                }
            }

            if ($request->filled('payment_status')) {
                $invoices->where('payment_status', $request->payment_status);
            }

            return datatables()->of($invoices)
                ->addColumn('invoice_type', function ($invoice) {
                    return '<span class="badge bg-' . $invoice->invoice_type_badge . ' fw-semibold">'
                        . $invoice->invoice_type_label . '</span>';
                })
                ->editColumn('vendor', function ($invoice) {
                    return $invoice->vendor->name ?? 'N/A';
                })
                ->editColumn('invoice_date', function ($invoice) {
                    return $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : 'N/A';
                })
                ->editColumn('due_date', function ($invoice) {
                    return $invoice->due_date
                        ? $invoice->due_date->format('d M Y')
                        : '<span class="text-muted">N/A</span>';
                })
                ->editColumn('total_amount', function ($invoice) {
                    return '৳ ' . number_format($invoice->total_amount, 2);
                })
                ->editColumn('paid_amount', function ($invoice) {
                    return '৳ ' . number_format($invoice->paid_amount, 2);
                })
                ->addColumn('outstanding', function ($invoice) {
                    return $invoice->getOutstandingAmount() > 0
                        ? '<span class="text-danger fw-bold">৳ ' . number_format($invoice->getOutstandingAmount(), 2) . '</span>'
                        : '<span class="text-success">৳ 0.00</span>';
                })
                ->editColumn('payment_status', function ($invoice) {
                    return '<span class="badge bg-' . $invoice->getPaymentStatusBadge() . '">'
                        . $invoice->getPaymentStatusLabel() . '</span>';
                })
                ->addColumn('actions', function ($invoice) {
                    return view('InvoiceManagement.partials.actions', compact('invoice'))->render();
                })
                ->rawColumns(['invoice_type', 'due_date', 'outstanding', 'payment_status', 'actions'])
                ->make(true);
        }

        return view('InvoiceManagement.index');
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create(Request $request)
    {
        $vendors           = Vendor::where('is_active', true)->orderBy('name')->get();
        $nextInvoiceNumber = Invoice::generateInvoiceNumber();

        // Pre-fill from maintenance if maintenance_id passed
        $maintenance = null;
        if ($request->filled('maintenance_id')) {
            $maintenance = \App\Models\VehicleMaintenance::with('vendor')
                ->findOrFail($request->maintenance_id);

            // Guard: already has invoice
            if ($maintenance->invoice_id) {
                return redirect()->route('invoices.show', $maintenance->invoice_id)
                    ->with('error', 'This maintenance already has an invoice.');
            }
        }

        // Pre-fill from rent if rent_id passed
        $rent = null;
        if ($request->filled('rent_id')) {
            $rent = \App\Models\RentBase::with(['agreement.vendor'])
                ->findOrFail($request->rent_id);

            // Guard: already has invoice
            if ($rent->invoice_id) {
                return redirect()->route('invoices.show', $rent->invoice_id)
                    ->with('error', 'This rent record already has an invoice.');
            }
        }

        return view('InvoiceManagement.create', compact('vendors', 'nextInvoiceNumber', 'maintenance', 'rent'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {   
        $validated = $request->validate([
            'maintenance_id'  => 'nullable|exists:vehicle_maintenances,id',
            'rent_id'         => 'nullable|exists:rent_base,id',
            'vendor_id'       => 'required|exists:vendors,id',
            'invoice_date'    => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:invoice_date',
            'subtotal'        => 'required|numeric|min:0',
            'tax_amount'      => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_status'  => 'required|in:pending,partial,paid,overdue',
            'paid_amount'     => 'nullable|numeric|min:0',
            'payment_date'    => 'nullable|date',
            'payment_method'  => 'nullable|in:cash,bank_transfer,check,card',
            'invoice_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks'         => 'nullable|string',
        ]);
        

        // Auto-calculate total
        $validated['total_amount'] = ($validated['subtotal'] ?? 0)
                                   + ($validated['tax_amount'] ?? 0)
                                   - ($validated['discount_amount'] ?? 0);

        // Defaults
        $validated['invoice_number']   = Invoice::generateInvoiceNumber();
        $validated['tax_amount']       = $validated['tax_amount'] ?? 0;
        $validated['discount_amount']  = $validated['discount_amount'] ?? 0;
        $validated['paid_amount']      = $validated['paid_amount'] ?? 0;

        // Handle file upload
        if ($request->hasFile('invoice_file')) {
            $validated['invoice_file_path'] = $request->file('invoice_file')
                ->store('invoices', 'public');
        }

        // Auto-set status to paid if full amount paid
        if ($validated['paid_amount'] >= $validated['total_amount']) {
            $validated['payment_status'] = 'paid';
        } elseif ($validated['paid_amount'] > 0) {
            $validated['payment_status'] = 'partial';
        }

        // Extract context IDs before creating invoice
        $maintenanceId = $validated['maintenance_id'] ?? null;
        $rentId = $validated['rent_id'] ?? null;
        unset($validated['maintenance_id'], $validated['rent_id']);

        $invoice = Invoice::create($validated);

        // Link invoice back to maintenance record
        if ($maintenanceId) {
            \App\Models\VehicleMaintenance::where('id', $maintenanceId)
                ->update(['invoice_id' => $invoice->id]);

            return redirect()->route('maintenance.maintenances.index')
                ->with('success', 'Invoice ' . $invoice->invoice_number . ' created and linked to maintenance successfully.');
        }

        // Link invoice back to rent record
        if ($rentId) {
            \App\Models\RentBase::where('id', $rentId)
                ->update(['invoice_id' => $invoice->id]);

            return redirect()->route('rent.index')
                ->with('success', 'Invoice ' . $invoice->invoice_number . ' created and linked to rent successfully.');
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'vendor',
            'maintenances.vehicle',
            'rentBases.agreement.vendor',
            'rentBases.agreement.utilities.utilityType',
            'rentBases.components',
            'rentBases.increments',
            'rentBases.securityDeposits'
        ]);

        return view('InvoiceManagement.show', compact('invoice'));
    }

    /**
     * Show the form for editing the invoice
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load([
            'vendor',
            'rentBases.agreement.vendor',
            'rentBases.agreement.utilities.utilityType',
            'rentBases.components',
            'rentBases.increments',
            'rentBases.securityDeposits'
        ]);
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $rent = $invoice->rentBases->first();

        return view('InvoiceManagement.edit', compact('invoice', 'vendors', 'rent'));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'vendor_id'       => 'required|exists:vendors,id',
            'invoice_date'    => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:invoice_date',
            'subtotal'        => 'required|numeric|min:0',
            'tax_amount'      => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_status'  => 'required|in:pending,partial,paid,overdue',
            'paid_amount'     => 'nullable|numeric|min:0',
            'payment_date'    => 'nullable|date',
            'payment_method'  => 'nullable|in:cash,bank_transfer,check,card',
            'invoice_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks'         => 'nullable|string',
        ]);

        // Auto-calculate total
        $validated['total_amount'] = ($validated['subtotal'] ?? 0)
                                   + ($validated['tax_amount'] ?? 0)
                                   - ($validated['discount_amount'] ?? 0);

        // Defaults
        $validated['tax_amount']      = $validated['tax_amount'] ?? 0;
        $validated['discount_amount'] = $validated['discount_amount'] ?? 0;
        $validated['paid_amount']     = $validated['paid_amount'] ?? 0;

        // Handle new file upload
        if ($request->hasFile('invoice_file')) {
            // Delete old file if exists
            if ($invoice->invoice_file_path) {
                Storage::disk('public')->delete($invoice->invoice_file_path);
            }
            $validated['invoice_file_path'] = $request->file('invoice_file')
                ->store('invoices', 'public');
        }

        // Auto-set status based on paid amount
        if ($validated['paid_amount'] >= $validated['total_amount']) {
            $validated['payment_status'] = 'paid';
        } elseif ($validated['paid_amount'] > 0) {
            $validated['payment_status'] = 'partial';
        }

        $invoice->update($validated);

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        // Delete file if exists
        if ($invoice->invoice_file_path) {
            Storage::disk('public')->delete($invoice->invoice_file_path);
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Record a payment against an invoice
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'paid_amount'    => 'required|numeric|min:0.01|max:' . $invoice->getOutstandingAmount(),
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,card',
        ]);

        $newPaidAmount = $invoice->paid_amount + $request->paid_amount;

        // Determine new status
        if ($newPaidAmount >= $invoice->total_amount) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $invoice->update([
            'paid_amount'    => $newPaidAmount,
            'payment_status' => $status,
            'payment_date'   => $request->payment_date,
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Payment of ৳ ' . number_format($request->paid_amount, 2) . ' recorded successfully.');
    }

    /**
     * Print or export industry-standard PDF layout
     */
    public function printInvoice(Invoice $invoice)
    {
        $invoice->load([
            'vendor',
            'maintenances.vehicle',
            'rentBases.agreement.vendor',
            'rentBases.agreement.utilities.utilityType',
            'rentBases.components',
            'rentBases.increments',
            'rentBases.securityDeposits'
        ]);

        return view('InvoiceManagement.pdf', compact('invoice'));
    }

    /**
     * Download / Save PDF invoice
     */
    public function download(Invoice $invoice)
    {
        $invoice->load([
            'vendor',
            'maintenances.vehicle',
            'rentBases.agreement.vendor',
            'rentBases.agreement.utilities.utilityType',
            'rentBases.components',
            'rentBases.increments',
            'rentBases.securityDeposits'
        ]);

        return view('InvoiceManagement.pdf', compact('invoice'));
    }
}