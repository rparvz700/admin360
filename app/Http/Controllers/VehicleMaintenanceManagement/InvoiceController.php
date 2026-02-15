<?php

namespace App\Http\Controllers\VehicleMaintenanceManagement;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $invoices = Invoice::with('vendor')
                ->orderByDesc('invoice_date')
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'vendor' => $invoice->vendor->name ?? 'N/A',
                        'invoice_date' => $invoice->invoice_date->format('d M Y'),
                        'due_date' => $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A',
                        'total_amount' => '৳ ' . number_format($invoice->total_amount, 2),
                        'paid_amount' => '৳ ' . number_format($invoice->paid_amount, 2),
                        'outstanding' => '৳ ' . number_format($invoice->getOutstandingAmount(), 2),
                        'payment_status' => '<span class="badge bg-' . $invoice->getPaymentStatusBadge() . '">' . 
                                          $invoice->getPaymentStatusLabel() . '</span>',
                        'is_overdue' => $invoice->isOverdue() ? 
                            '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Overdue</span>' : '',
                        'actions' => view('maintenance.invoices.partials.actions', compact('invoice'))->render(),
                    ];
                });

            return response()->json(['data' => $invoices]);
        }

        return view('maintenance.invoices.index');
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create()
    {
        $vendors = Vendor::active()->get();
        $invoice_number = Invoice::generateInvoiceNumber();

        return view('maintenance.invoices.create', compact('vendors', 'invoice_number'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid,overdue',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,check,card',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks' => 'nullable|string',
        ]);

        // Calculate total amount
        $validated['total_amount'] = $validated['subtotal'] 
            + ($validated['tax_amount'] ?? 0) 
            - ($validated['discount_amount'] ?? 0);

        $validated['invoice_number'] = Invoice::generateInvoiceNumber();
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

        // Handle file upload
        if ($request->hasFile('invoice_file')) {
            $file = $request->file('invoice_file');
            $filename = time() . '_' . $validated['invoice_number'] . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('invoices', $filename, 'public');
            $validated['invoice_file_path'] = $path;
        }

        Invoice::create($validated);

        return redirect()->route('maintenance.invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['vendor', 'maintenances.vehicle']);

        return view('maintenance.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the invoice
     */
    public function edit(Invoice $invoice)
    {
        $vendors = Vendor::active()->get();

        return view('maintenance.invoices.edit', compact('invoice', 'vendors'));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid,overdue',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,check,card',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks' => 'nullable|string',
        ]);

        // Calculate total amount
        $validated['total_amount'] = $validated['subtotal'] 
            + ($validated['tax_amount'] ?? 0) 
            - ($validated['discount_amount'] ?? 0);

        // Handle file upload
        if ($request->hasFile('invoice_file')) {
            // Delete old file if exists
            if ($invoice->invoice_file_path) {
                Storage::disk('public')->delete($invoice->invoice_file_path);
            }

            $file = $request->file('invoice_file');
            $filename = time() . '_' . $invoice->invoice_number . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('invoices', $filename, 'public');
            $validated['invoice_file_path'] = $path;
        }

        $invoice->update($validated);

        return redirect()->route('maintenance.invoices.index')
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        // Check if invoice is linked to any maintenance
        if ($invoice->maintenances()->count() > 0) {
            return redirect()->route('maintenance.invoices.index')
                ->with('error', 'Cannot delete invoice linked to maintenance records.');
        }

        // Delete file if exists
        if ($invoice->invoice_file_path) {
            Storage::disk('public')->delete($invoice->invoice_file_path);
        }

        $invoice->delete();

        return redirect()->route('maintenance.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0|max:' . $invoice->getOutstandingAmount(),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,card',
            'remarks' => 'nullable|string',
        ]);

        $newPaidAmount = $invoice->paid_amount + $validated['paid_amount'];
        $paymentStatus = 'partial';

        if ($newPaidAmount >= $invoice->total_amount) {
            $paymentStatus = 'paid';
        }

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'remarks' => $validated['remarks'],
        ]);

        return redirect()->route('maintenance.invoices.show', $invoice)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Download invoice file
     */
    public function download(Invoice $invoice)
    {
        if (!$invoice->invoice_file_path || !Storage::disk('public')->exists($invoice->invoice_file_path)) {
            return back()->with('error', 'Invoice file not found.');
        }

        return Storage::disk('public')->download($invoice->invoice_file_path, $invoice->invoice_number . '.pdf');
    }
}
