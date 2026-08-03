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
        $this->middleware('permission:create-invoice', ['only' => ['create', 'store', 'bulkGenerateForm', 'previewBulkGenerate', 'bulkGenerate']]);
        $this->middleware('permission:edit-invoice', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-invoice', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $invoices = Invoice::with([
                'vendor',
                'maintenances.vehicle',
                'rentBases.agreement.floors.building',
                'rentBasePivot.agreement.floors.building'
            ])->orderByDesc('invoice_date');

            if ($request->filled('invoice_type')) {
                $type = $request->invoice_type;
                if ($type === 'rent') {
                    $invoices->where(function ($q) {
                        $q->has('rentBases')->orWhereHas('rentBasePivot');
                    });
                } elseif ($type === 'maintenance') {
                    $invoices->has('maintenances');
                } elseif ($type === 'general') {
                    $invoices->doesntHave('rentBases')->doesntHave('rentBasePivot')->doesntHave('maintenances');
                }
            }

            if ($request->filled('payment_status')) {
                $invoices->where('payment_status', $request->payment_status);
            }

            if ($request->filled('billing_month')) {
                $month = $request->billing_month;
                $parts = explode('-', $month);
                $year  = $parts[0] ?? null;
                $m     = $parts[1] ?? null;

                $invoices->where(function ($q) use ($month, $year, $m) {
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

            return datatables()->of($invoices)
                ->addColumn('invoice_type', function ($invoice) {
                    return '<span class="badge bg-' . $invoice->invoice_type_badge . ' fw-semibold">'
                        . $invoice->invoice_type_label . '</span>';
                })
                ->filterColumn('invoice_number', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->whereRaw("LOWER(invoice_number) LIKE ?", ["%{$lower}%"]);
                })
                ->addColumn('item_details', function ($invoice) {
                    return $invoice->invoice_item_html;
                })
                ->filterColumn('item_details', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->where(function ($q) use ($lower) {
                        $q->whereHas('rentBases.agreement', function ($q2) use ($lower) {
                            $q2->whereRaw("LOWER(agreement_ref_no) LIKE ?", ["%{$lower}%"])
                               ->orWhereHas('floors.building', function ($q3) use ($lower) {
                                   $q3->whereRaw("LOWER(site_code) LIKE ?", ["%{$lower}%"])
                                      ->orWhereRaw("LOWER(code) LIKE ?", ["%{$lower}%"])
                                      ->orWhereRaw("LOWER(site_name) LIKE ?", ["%{$lower}%"]);
                               });
                        })
                        ->orWhereHas('rentBasePivot.agreement', function ($q2) use ($lower) {
                            $q2->whereRaw("LOWER(agreement_ref_no) LIKE ?", ["%{$lower}%"])
                               ->orWhereHas('floors.building', function ($q3) use ($lower) {
                                   $q3->whereRaw("LOWER(site_code) LIKE ?", ["%{$lower}%"])
                                      ->orWhereRaw("LOWER(code) LIKE ?", ["%{$lower}%"])
                                      ->orWhereRaw("LOWER(site_name) LIKE ?", ["%{$lower}%"]);
                               });
                        })
                        ->orWhereHas('maintenances', function ($q2) use ($lower) {
                            $q2->whereRaw("LOWER(service_description) LIKE ?", ["%{$lower}%"])
                               ->orWhereHas('vehicle', function ($q3) use ($lower) {
                                   $q3->whereRaw("LOWER(registration_number) LIKE ?", ["%{$lower}%"]);
                               });
                        })
                        ->orWhereRaw("LOWER(remarks) LIKE ?", ["%{$lower}%"]);
                    });
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
                ->rawColumns(['invoice_type', 'item_details', 'outstanding', 'payment_status', 'actions'])
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

        if ($rentId && empty($validated['billing_month'])) {
            $validated['billing_month'] = \Carbon\Carbon::parse($validated['invoice_date'])->format('Y-m');
        }

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

            $invoice->rentBasePivot()->syncWithoutDetaching([
                $rentId => ['billing_month' => $validated['billing_month']]
            ]);

            return redirect()->route('rent.index')
                ->with('success', 'Invoice ' . $invoice->invoice_number . ' created and linked to rent successfully.');
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Show form for bulk generating monthly rent invoices
     */
    public function bulkGenerateForm()
    {
        $this->ensureSpreadsheetAssetsExist();
        return view('InvoiceManagement.bulk_generate');
    }

    /**
     * Preview bulk rent invoice generation data for a selected month (AJAX)
     */
    public function previewBulkGenerate(Request $request)
    {
        $request->validate([
            'billing_month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $billingMonth = $request->billing_month;
        $defaultInvoiceDate = $billingMonth . '-01';

        $rentBases = \App\Models\RentBase::whereHas('agreement', function ($q) {
            $q->where('status', 1);
        })
        ->with(['agreement.vendor', 'agreement.floors.building', 'increments', 'invoices'])
        ->get();

        $data = [];
        $meta = [];
        $totalCount = $rentBases->count();
        $pendingCount = 0;
        $alreadyCount = 0;
        $missingVendorCount = 0;

        foreach ($rentBases as $index => $rent) {
            $sl = $index + 1;
            $agreementRef = $rent->agreement->agreement_ref_no ?? 'N/A';
            $vendorId     = $rent->agreement->vendor_id ?? null;
            $vendorName   = $rent->agreement->vendor->name ?? 'Vendor Not Assigned';
            $rentType     = ucfirst($rent->rent_type ?? 'N/A');

            // Extract Site Code, Building Name, and Floor Information from agreement's floors
            $floors = $rent->agreement->floors ?? collect();

            $siteCodes = $floors->map(fn($f) => $f->building->site_code ?? $f->building->code ?? null)->filter()->unique()->implode(', ');
            $siteCode  = $siteCodes ?: 'N/A';

            $buildingNames = $floors->map(fn($f) => $f->building->site_name ?? null)->filter()->unique()->implode(', ');
            $buildingName  = $buildingNames ?: 'N/A';

            $floorLabels = $floors->pluck('floor_label')->filter()->unique()->implode(', ');
            $floorInfo   = $floorLabels ?: 'N/A';

            $detailsBtn = '<button type="button" class="btn btn-sm btn-alt-info btn-rent-details px-1 py-0" data-rent-id="' . $rent->id . '" title="View Rent Segregation, Utilities, Increments & Deposits"><i class="fa fa-eye"></i></button>';

            $calc = $rent->getEffectiveRentForMonth($billingMonth);

            // Check if already invoiced for this billing month
            $existingInvoice = $rent->invoices->first(function ($inv) use ($billingMonth) {
                return $inv->pivot->billing_month === $billingMonth || $inv->billing_month === $billingMonth;
            });

            $isAlreadyInvoiced = (bool) $existingInvoice;

            if (!$vendorId) {
                $missingVendorCount++;
                $status = 'missing_vendor';
                $remarks = '⚠️ Vendor not assigned on Agreement';
                $isChecked = false;
            } elseif ($isAlreadyInvoiced) {
                $alreadyCount++;
                $status = 'already_invoiced';
                $remarks = 'Already Invoiced: ' . $existingInvoice->invoice_number;
                $isChecked = false;
            } else {
                $pendingCount++;
                $status = 'pending';
                $remarks = '';
                $isChecked = true;
            }

            $data[] = [
                $isChecked,                                 // 0: Checkbox
                $agreementRef,                              // 1: Agreement Ref
                $siteCode,                                  // 2: Site Code
                $buildingName,                              // 3: Building Name
                $floorInfo,                                 // 4: Floor Info
                $vendorName,                                // 5: Vendor
                $detailsBtn,                                // 6: Breakdown Button
                $rentType,                                  // 7: Rent Type
                number_format($calc['base_rent'], 2, '.', ''),        // 8: Base Rent
                number_format($calc['increment_amount'], 2, '.', ''), // 9: Increment Amount
                number_format($calc['effective_rent'], 2, '.', ''),   // 10: Effective Rent
                number_format($calc['vat'], 2, '.', ''),              // 11: VAT
                number_format($calc['tax'], 2, '.', ''),              // 12: Tax
                number_format($calc['subtotal'], 2, '.', ''),         // 13: Subtotal
                '0.00',                                     // 14: Discount (editable)
                number_format($calc['subtotal'], 2, '.', ''),         // 15: Total (subtotal - discount)
                $defaultInvoiceDate,                        // 16: Invoice Date (editable)
                '',                                         // 17: Due Date (editable)
                $remarks,                                   // 18: Remarks (editable)
            ];

            $meta[] = [
                'rent_base_id'            => $rent->id,
                'vendor_id'               => $vendorId,
                'status'                  => $status,
                'existing_invoice_number' => $existingInvoice->invoice_number ?? null,
            ];
        }

        return response()->json([
            'data'    => $data,
            'meta'    => $meta,
            'summary' => [
                'total'            => $totalCount,
                'pending'          => $pendingCount,
                'already_invoiced' => $alreadyCount,
                'missing_vendor'   => $missingVendorCount,
            ]
        ]);
    }

    /**
     * Fetch rent breakdown modal content via AJAX
     */
    public function rentBreakdownModal(\App\Models\RentBase $rent)
    {
        $rent->load([
            'agreement.vendor',
            'agreement.floors.building',
            'increments',
            'components',
            'securityDeposits',
            'agreementUtilities.utilityType'
        ]);

        $floors = $rent->agreement->floors ?? collect();

        $siteCodes = $floors->map(fn($f) => $f->building->site_code ?? $f->building->code ?? null)->filter()->unique()->implode(', ');
        $siteCode  = $siteCodes ?: 'N/A';

        $buildingNames = $floors->map(fn($f) => $f->building->site_name ?? null)->filter()->unique()->implode(', ');
        $buildingName  = $buildingNames ?: 'N/A';

        $floorLabels = $floors->pluck('floor_label')->filter()->unique()->implode(', ');
        $floorInfo   = $floorLabels ?: 'N/A';

        return view('InvoiceManagement.partials.rent_breakdown_modal', compact('rent', 'siteCode', 'buildingName', 'floorInfo'));
    }

    /**
     * Store bulk generated rent invoices
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'billing_month'       => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'invoices'            => ['required', 'array', 'min:1'],
            'invoices.*.rent_base_id' => ['required', 'exists:rent_base,id'],
            'invoices.*.discount'     => ['nullable', 'numeric', 'min:0'],
            'invoices.*.invoice_date' => ['required', 'date'],
            'invoices.*.due_date'     => ['nullable', 'date', 'after_or_equal:invoices.*.invoice_date'],
            'invoices.*.remarks'      => ['nullable', 'string'],
        ]);

        $billingMonth = $request->billing_month;
        $rows = $request->invoices;
        $createdInvoices = [];
        $skippedNoVendor = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($billingMonth, $rows, &$createdInvoices, &$skippedNoVendor) {
            foreach ($rows as $row) {
                $rentBase = \App\Models\RentBase::with('agreement.vendor')->findOrFail($row['rent_base_id']);

                $vendorId = $rentBase->agreement->vendor_id ?? null;
                if (!$vendorId) {
                    $skippedNoVendor++;
                    continue;
                }

                // Guard: Skip if already invoiced for this month
                if ($rentBase->hasInvoiceForMonth($billingMonth)) {
                    continue;
                }

                $calc = $rentBase->getEffectiveRentForMonth($billingMonth);
                $discount = is_numeric($row['discount'] ?? null) ? (float) $row['discount'] : 0.0;
                $subtotal = $calc['subtotal'];
                $total = max(0, $subtotal - $discount);

                $invoice = Invoice::create([
                    'invoice_number'  => Invoice::generateInvoiceNumber(),
                    'vendor_id'       => $vendorId,
                    'invoice_date'    => $row['invoice_date'],
                    'due_date'        => !empty($row['due_date']) ? $row['due_date'] : null,
                    'subtotal'        => $subtotal,
                    'tax_amount'      => 0,
                    'discount_amount' => $discount,
                    'total_amount'    => $total,
                    'payment_status'  => 'pending',
                    'paid_amount'     => 0,
                    'billing_month'   => $billingMonth,
                    'remarks'         => !empty($row['remarks']) ? $row['remarks'] : ('Rent Requisition for ' . $billingMonth),
                ]);

                // Attach to pivot
                $invoice->rentBasePivot()->attach($rentBase->id, [
                    'billing_month' => $billingMonth,
                ]);

                // Update single FK for backward compatibility
                $rentBase->update(['invoice_id' => $invoice->id]);

                $createdInvoices[] = $invoice->invoice_number;
            }
        });

        $count = count($createdInvoices);

        if ($count === 0) {
            $msg = 'No new invoices were generated.';
            if ($skippedNoVendor > 0) {
                $msg .= " {$skippedNoVendor} item(s) skipped because no vendor is assigned to their agreement.";
            }
            return response()->json([
                'success' => false,
                'message' => $msg,
            ], 422);
        }

        $msg = "{$count} rent requisition invoice(s) generated successfully.";
        if ($skippedNoVendor > 0) {
            $msg .= " ({$skippedNoVendor} skipped due to missing vendor)";
        }

        return response()->json([
            'success'  => true,
            'message'  => $msg,
            'redirect' => route('invoices.index'),
        ]);
    }

    /**
     * Ensure jspreadsheet local assets exist in public/js/plugins/jspreadsheet
     */
    private function ensureSpreadsheetAssetsExist()
    {
        $dir = public_path('js/plugins/jspreadsheet');
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }

        $files = [
            'jexcel.js'   => 'https://bossanova.uk/jspreadsheet/v4/jexcel.js',
            'jexcel.css'  => 'https://bossanova.uk/jspreadsheet/v4/jexcel.css',
            'jsuites.js'  => 'https://jsuites.net/v5/jsuites.js',
            'jsuites.css' => 'https://jsuites.net/v5/jsuites.css',
        ];

        foreach ($files as $filename => $url) {
            $path = $dir . '/' . $filename;
            if (!file_exists($path) || filesize($path) === 0) {
                $content = @file_get_contents($url);
                if ($content) {
                    @file_put_contents($path, $content);
                }
            }
        }
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
            'rentBases.agreement.floors.building',
            'rentBases.agreement.utilities.utilityType',
            'rentBases.components',
            'rentBases.increments',
            'rentBases.securityDeposits',
            'rentBasePivot.agreement.floors.building',
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

        return redirect()->back()
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