<?php

namespace App\Http\Controllers\InvoiceManagement;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\RentBase;
use App\Models\Vendor;
use App\Models\VatTax;
use App\Models\Agreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class RentInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:rent-invoice-management|invoice-management|create-rent-invoice|create-invoice|edit-rent-invoice|edit-invoice|delete-rent-invoice|delete-invoice', ['only' => ['index', 'show', 'recordPayment', 'download']]);
        $this->middleware('permission:create-rent-invoice|create-invoice', ['only' => ['create', 'store', 'bulkGenerateForm', 'previewBulkGenerate', 'bulkGenerate']]);
        $this->middleware('permission:edit-rent-invoice|edit-invoice', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-rent-invoice|delete-invoice', ['only' => ['destroy']]);
    }

    /**
     * Scope query to Rent Requisition Invoices only
     */
    private function rentInvoiceQuery()
    {
        return Invoice::where(function ($q) {
            $q->has('rentBases')->orWhereHas('rentBasePivot');
        });
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $invoices = $this->rentInvoiceQuery()->with([
                'vendor',
                'rentBases.agreement.floors.building',
                'rentBasePivot.agreement.floors.building'
            ])->orderByDesc('invoice_date');

            if ($request->filled('payment_status') && $request->payment_status !== 'all') {
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

            return DataTables::of($invoices)
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
                        ->orWhereRaw("LOWER(remarks) LIKE ?", ["%{$lower}%"]);
                    });
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
                    $showUrl  = route('invoices.rent.show', $invoice->id);
                    $printUrl = route('invoices.rent.print', $invoice->id);

                    $html = '<div class="dropdown d-inline-block">';
                    $html .= '<button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="rentActions' . $invoice->id . '" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                    $html .= '<div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="rentActions' . $invoice->id . '">';

                    $html .= '<a class="dropdown-item py-1" href="' . $showUrl . '"><i class="fa fa-eye text-info me-2"></i> View Details</a>';
                    $html .= '<a class="dropdown-item py-1" href="' . $printUrl . '" target="_blank"><i class="fa fa-print text-secondary me-2"></i> Print Invoice</a>';

                    if (auth()->user()->can('edit-rent-invoice') || auth()->user()->can('edit-invoice')) {
                        $html .= '<a class="dropdown-item py-1" href="' . route('invoices.rent.edit', $invoice->id) . '"><i class="fa fa-edit text-warning me-2"></i> Edit Invoice</a>';
                    }

                    if ($invoice->payment_status !== 'paid') {
                        $html .= '<a class="dropdown-item py-1 btn-record-payment text-success" href="javascript:void(0)" data-id="' . $invoice->id . '" data-number="' . e($invoice->invoice_number) . '" data-outstanding="' . number_format($invoice->getOutstandingAmount(), 2) . '"><i class="fa fa-credit-card me-2"></i> Record Payment</a>';
                    }

                    if (auth()->user()->can('delete-rent-invoice') || auth()->user()->can('delete-invoice')) {
                        $html .= '<div class="dropdown-divider"></div>';
                        $html .= '<form action="' . route('invoices.rent.destroy', $invoice->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this rent invoice?\');">';
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

        return view('InvoiceManagement.Rent.index');
    }

    public function show($id)
    {
        $invoice = $this->rentInvoiceQuery()->with([
            'vendor',
            'rentBases.agreement.floors.building',
            'rentBasePivot.agreement.floors.building'
        ])->findOrFail($id);

        return view('InvoiceManagement.Rent.show', compact('invoice'));
    }

    public function printInvoice($id)
    {
        $invoice = $this->rentInvoiceQuery()->with([
            'vendor',
            'rentBases.agreement.vendor',
            'rentBases.agreement.floors.building',
            'rentBases.agreement.utilities.utilityType',
            'rentBases.components',
            'rentBases.increments',
            'rentBases.securityDeposits',
            'rentBasePivot.agreement.vendor',
            'rentBasePivot.agreement.floors.building',
            'rentBasePivot.agreement.utilities.utilityType',
            'rentBasePivot.components',
            'rentBasePivot.increments',
            'rentBasePivot.securityDeposits',
        ])->findOrFail($id);

        return view('InvoiceManagement.Rent.print', compact('invoice'));
    }

    public function recordPayment(Request $request, $id)
    {
        $invoice = $this->rentInvoiceQuery()->findOrFail($id);

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

        return redirect()->back()->with('success', 'Payment recorded successfully for Rent Invoice #' . $invoice->invoice_number);
    }

    public function destroy($id)
    {
        $invoice = $this->rentInvoiceQuery()->findOrFail($id);
        $invoice->delete();

        return redirect()->route('invoices.rent.index')->with('success', 'Rent Invoice deleted successfully.');
    }

    // Bulk Generation for Rent
    public function bulkGenerateForm()
    {
        return view('InvoiceManagement.Rent.bulk_generate');
    }

    public function previewBulkGenerate(Request $request)
    {
        $request->validate([
            'billing_month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $billingMonth = $request->billing_month;
        $defaultInvoiceDate = $billingMonth . '-01';

        $rentBases = RentBase::whereHas('agreement', function ($q) {
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

            $floors = $rent->agreement->floors ?? collect();

            $siteCodes = $floors->map(fn($f) => $f->building->site_code ?? $f->building->code ?? null)->filter()->unique()->implode(', ');
            $siteCode  = $siteCodes ?: 'N/A';

            $buildingNames = $floors->map(fn($f) => $f->building->site_name ?? null)->filter()->unique()->implode(', ');
            $buildingName  = $buildingNames ?: 'N/A';

            $floorLabels = $floors->pluck('floor_label')->filter()->unique()->implode(', ');
            $floorInfo   = $floorLabels ?: 'N/A';

            $detailsBtn = '<button type="button" class="btn btn-sm btn-alt-info btn-rent-details px-1 py-0" data-rent-id="' . $rent->id . '" title="View Rent Segregation, Utilities, Increments & Deposits"><i class="fa fa-eye"></i></button>';

            $calc = $rent->getEffectiveRentForMonth($billingMonth);

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

        DB::transaction(function () use ($billingMonth, $rows, &$createdInvoices, &$skippedNoVendor) {
            foreach ($rows as $row) {
                $rentBase = RentBase::with('agreement.vendor')->findOrFail($row['rent_base_id']);

                $vendorId = $rentBase->agreement->vendor_id ?? null;
                if (!$vendorId) {
                    $skippedNoVendor++;
                    continue;
                }

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

                $invoice->rentBasePivot()->attach($rentBase->id, [
                    'billing_month' => $billingMonth,
                ]);

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
            'redirect' => route('invoices.rent.index'),
        ]);
    }

    public function rentBreakdownModal($rentId)
    {
        $rent = RentBase::with(['agreement.vendor', 'agreement.floors.building', 'increments', 'components'])->findOrFail($rentId);

        $floors = $rent->agreement->floors ?? collect();
        $siteCodes = $floors->map(fn($f) => $f->building->site_code ?? $f->building->code ?? null)->filter()->unique()->implode(', ');
        $siteCode  = $siteCodes ?: 'N/A';

        $buildingNames = $floors->map(fn($f) => $f->building->site_name ?? null)->filter()->unique()->implode(', ');
        $buildingName  = $buildingNames ?: 'N/A';

        $floorLabels = $floors->pluck('floor_label')->filter()->unique()->implode(', ');
        $floorInfo   = $floorLabels ?: 'N/A';

        return view('InvoiceManagement.partials.rent_breakdown_modal', compact('rent', 'siteCode', 'buildingName', 'floorInfo'));
    }
}
