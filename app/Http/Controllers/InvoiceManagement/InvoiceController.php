<?php

namespace App\Http\Controllers\InvoiceManagement;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Start with your base query builder, including relationships
            // Yajra DataTables will handle the orderBy, pagination, searching, etc.
            $query = Invoice::with('vendor');

            // Now, pass the query to DataTables
            return DataTables::of($query)
                // Add your custom columns or modify existing ones
                // invoice_number and id are direct attributes, Yajra handles them by default

                ->addColumn('vendor', function ($invoice) {
                    return $invoice->vendor->name ?? 'N/A';
                })
                ->addColumn('invoice_date', function ($invoice) {
                    // Ensure invoice_date is a Carbon instance, or handle null gracefully
                    return $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '';
                })
                ->addColumn('due_date', function ($invoice) {
                    return $invoice->due_date
                                    ? $invoice->due_date->format('d M Y')
                                    : '<span class="text-muted">N/A</span>';
                })
                ->addColumn('total_amount', function ($invoice) {
                    return '৳ ' . number_format($invoice->total_amount, 2);
                })
                ->addColumn('paid_amount', function ($invoice) {
                    return '৳ ' . number_format($invoice->paid_amount, 2);
                })
                ->addColumn('outstanding', function ($invoice) {
                    $outstanding = $invoice->getOutstandingAmount();
                    return $outstanding > 0
                                    ? '<span class="text-danger">৳ ' . number_format($outstanding, 2) . '</span>'
                                    : '<span class="text-success">৳ 0.00</span>';
                })
                ->addColumn('payment_status', function ($invoice) {
                    return '<span class="badge bg-' . $invoice->getPaymentStatusBadge() . '">'
                                    . $invoice->getPaymentStatusLabel() . '</span>';
                })
                ->addColumn('actions', function ($invoice) {
                    // Keep your partial view for actions, it's a good practice
                    return view('InvoiceManagement.partials.actions', compact('invoice'))->render();
                })
                // Specify any columns that contain raw HTML to prevent escaping
                ->rawColumns(['due_date', 'outstanding', 'payment_status', 'actions'])
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

        return view('InvoiceManagement.create', compact('vendors', 'nextInvoiceNumber', 'maintenance'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {   
        $validated = $request->validate([
            'maintenance_id'  => 'nullable|exists:vehicle_maintenances,id',
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

        // Remove maintenance_id before creating invoice (not a column on invoices table)
        $maintenanceId = $validated['maintenance_id'] ?? null;
        unset($validated['maintenance_id']);

        $invoice = Invoice::create($validated);

        // Link invoice back to maintenance record
        if ($maintenanceId) {
            \App\Models\VehicleMaintenance::where('id', $maintenanceId)
                ->update(['invoice_id' => $invoice->id]);

            return redirect()->route('maintenance.maintenances.index')
                ->with('success', 'Invoice ' . $invoice->invoice_number . ' created and linked to maintenance successfully.');
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['vendor', 'maintenances.vehicle']);

        return view('InvoiceManagement.show', compact('invoice'));
    }

    /**
     * Show the form for editing the invoice
     */
    public function edit(Invoice $invoice)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('InvoiceManagement.edit', compact('invoice', 'vendors'));
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
}