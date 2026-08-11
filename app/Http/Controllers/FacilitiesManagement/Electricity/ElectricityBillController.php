<?php

namespace App\Http\Controllers\FacilitiesManagement\Electricity;

use App\Http\Controllers\Controller;
use App\Models\ElectricityBill;
use App\Models\ElectricityMeter;
use App\Models\PropertiesBuilding;
use App\Models\Rio;
use App\Models\Project;
use App\Services\ElectricityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class ElectricityBillController extends Controller
{
    protected ElectricityService $electricityService;

    public function __construct(ElectricityService $electricityService)
    {
        $this->middleware('auth');
        $this->electricityService = $electricityService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ElectricityBill::with(['meter', 'building.rio', 'creator', 'payer'])->latest();

            // Filter by RIO
            if ($request->filled('rio_id') && $request->rio_id !== 'all') {
                $query->where('rio_id', $request->rio_id);
            }

            // Filter by Project
            if ($request->filled('project_name') && $request->project_name !== 'all') {
                $query->where('project_name', $request->project_name);
            }

            // Filter by Status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by Bill Type
            if ($request->filled('bill_type') && $request->bill_type !== 'all') {
                $query->where('bill_type', $request->bill_type);
            }

            // Filter by Month
            if ($request->filled('billing_month')) {
                $time = strtotime($request->billing_month . '-01');
                if ($time) {
                    $query->where('billing_month', date("M'y", $time));
                } else {
                    $query->where('billing_month', $request->billing_month);
                }
            }

            return DataTables::of($query)
                ->addColumn('requisition_no', function ($bill) {
                    return '<a href="' . route('electricity.bills.show', $bill->id) . '" class="fw-bold text-primary">'
                         . $bill->requisition_no . '</a>';
                })
                ->filterColumn('requisition_no', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->whereRaw("LOWER(requisition_no) LIKE ?", ["%{$lower}%"]);
                })
                ->addColumn('site', function ($bill) {
                    $siteName = $bill->building->site_name ?? 'N/A';
                    $code     = $bill->building->code ?? $bill->building->site_code;
                    return '<div class="fw-semibold text-dark">' . e($siteName) . '</div>' 
                         . ($code ? '<div class="fs-xs text-muted">' . e($code) . '</div>' : '');
                })
                ->filterColumn('site', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->whereHas('building', function ($q) use ($lower) {
                        $q->whereRaw("LOWER(site_name) LIKE ?", ["%{$lower}%"])
                          ->orWhereRaw("LOWER(site_code) LIKE ?", ["%{$lower}%"])
                          ->orWhereRaw("LOWER(code) LIKE ?", ["%{$lower}%"]);
                    });
                })
                ->addColumn('rio', function ($bill) {
                    return $bill->rio->name ?? ($bill->building->rio->name ?? 'N/A');
                })
                ->filterColumn('rio', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->where(function ($q) use ($lower) {
                        $q->whereHas('rio', function ($q2) use ($lower) {
                            $q2->whereRaw("LOWER(name) LIKE ?", ["%{$lower}%"]);
                        })
                        ->orWhereHas('building.rio', function ($q2) use ($lower) {
                            $q2->whereRaw("LOWER(name) LIKE ?", ["%{$lower}%"]);
                        });
                    });
                })
                ->addColumn('meter', function ($bill) {
                    $meterNo  = $bill->meter->meter_number ?? 'N/A';
                    $billType = ucfirst($bill->bill_type ?? '');
                    return '<div class="fw-semibold text-dark">' . e($meterNo) . '</div>'
                         . ($billType ? '<div class="fs-xs text-muted">' . e($billType) . '</div>' : '');
                })
                ->filterColumn('meter', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->where(function ($q) use ($lower) {
                        $q->whereHas('meter', function ($q2) use ($lower) {
                            $q2->whereRaw("LOWER(meter_number) LIKE ?", ["%{$lower}%"]);
                        })
                        ->orWhereRaw("LOWER(bill_type) LIKE ?", ["%{$lower}%"]);
                    });
                })
                ->addColumn('total_amount_formatted', function ($bill) {
                    return '৳ ' . number_format($bill->total_amount, 2);
                })
                ->filterColumn('total_amount_formatted', function ($query, $keyword) {
                    $query->where('total_amount', 'like', "%{$keyword}%");
                })
                ->editColumn('status', function ($bill) {
                    return '<span class="badge bg-' . $bill->status_badge . '">' . $bill->status_label . '</span>';
                })
                ->addColumn('actions', function ($bill) {
                    $id = $bill->id;
                    $html = '<div class="dropdown d-inline-block">';
                    $html .= '<button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="billActions' . $id . '" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                    $html .= '<div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="billActions' . $id . '">';
                    
                    $html .= '<a class="dropdown-item py-1" href="' . route('electricity.bills.show', $id) . '"><i class="fa fa-eye text-info me-2"></i> View Details</a>';
                    $html .= '<a class="dropdown-item py-1" href="' . route('electricity.bills.print', $id) . '" target="_blank"><i class="fa fa-print text-secondary me-2"></i> Print Requisition Sheet</a>';
                    
                    if ($bill->status === 'generated') {
                        $html .= '<a class="dropdown-item py-1 mark-paid-btn text-success" href="javascript:void(0)" data-id="' . $id . '" data-req="' . e($bill->requisition_no) . '" data-amount="' . number_format($bill->total_amount, 2) . '"><i class="fa fa-check-circle me-2"></i> Record Payment</a>';
                    }

                    $html .= '</div></div>';
                    return $html;
                })
                ->rawColumns(['requisition_no', 'site', 'meter', 'status', 'actions'])
                ->make(true);
        }

        $rios = Rio::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        return view('FacilitiesManagement.Electricity.Bills.index', compact('rios', 'projects'));
    }

    public function create()
    {
        $meters = ElectricityMeter::with(['building.rio', 'vendor'])
            ->where('is_active', true)
            ->orderBy('meter_number')
            ->get();
        
        $buildings = PropertiesBuilding::with('rio')->orderBy('site_name')->get();
        $rios = Rio::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('FacilitiesManagement.Electricity.Bills.create', compact('meters', 'buildings', 'rios', 'projects'));
    }

    public function getPreviousReading($meterId)
    {
        $meter = ElectricityMeter::with(['building.rio', 'floor.project', 'floors.project'])->find($meterId);

        $lastPostpaidBill = ElectricityBill::where('meter_id', $meterId)
            ->where('bill_type', 'postpaid')
            ->latest('id')
            ->first();
        
        $prevOffpeak = $lastPostpaidBill ? (float) $lastPostpaidBill->current_reading : 0.00;
        $prevPeak = $lastPostpaidBill ? (float) $lastPostpaidBill->current_peak_reading : 0.00;

        $lastPrepaidBill = null;
        if ($meter && $meter->meter_type === 'prepaid') {
            $lastPrepaidBill = ElectricityBill::where('meter_id', $meterId)
                ->where('bill_type', 'prepaid')
                ->latest('id')
                ->first();
        }

        $projectName = null;
        if ($meter) {
            if ($meter->floor && $meter->floor->project) {
                $projectName = $meter->floor->project->name;
            } elseif ($meter->floors && $meter->floors->isNotEmpty()) {
                foreach ($meter->floors as $fl) {
                    if ($fl->project) {
                        $projectName = $fl->project->name;
                        break;
                    }
                }
            }
        }

        return response()->json([
            'previous_reading' => $prevOffpeak,
            'previous_peak_reading' => $prevPeak,
            'meter' => $meter,
            'project_name' => $projectName,
            'last_prepaid_bill' => $lastPrepaidBill ? [
                'recharge_amount' => $lastPrepaidBill->recharge_amount,
                'recharge_date' => $lastPrepaidBill->recharge_date ? $lastPrepaidBill->recharge_date->format('Y-m-d') : ($lastPrepaidBill->created_at ? $lastPrepaidBill->created_at->format('Y-m-d') : null),
                'current_balance' => $lastPrepaidBill->current_balance,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'meter_id'                => 'required|exists:electricity_meters,id',
            'bill_type'               => 'required|in:postpaid,prepaid',
            'project_name'            => 'required|string|max:100',
            'billing_month'           => 'required|string|max:20',
            
            // Postpaid fields (Off-Peak / Flat is required, Peak is optional)
            'previous_reading'        => 'nullable|numeric|min:0',
            'current_reading'         => 'required_if:bill_type,postpaid|nullable|numeric|min:0',
            'units_consumed'          => 'nullable|numeric|min:0',
            'rate_per_unit'           => 'required_if:bill_type,postpaid|nullable|numeric|min:0',
            
            'previous_peak_reading'   => 'nullable|numeric|min:0',
            'current_peak_reading'    => 'nullable|numeric|min:0',
            'units_peak_consumed'     => 'nullable|numeric|min:0',
            'rate_peak_per_unit'      => 'nullable|numeric|min:0',

            'net_amount'              => 'required_if:bill_type,postpaid|nullable|numeric|min:0',
            'vat_amount'              => 'nullable|numeric|min:0',
            'late_fee'                => 'nullable|numeric|min:0',
            'meter_charge'            => 'nullable|numeric|min:0',
            'others_amount'           => 'nullable|numeric|min:0',
            'total_amount'            => 'required_if:bill_type,postpaid|nullable|numeric|min:0',
            'received_subcenter_date' => 'nullable|date',
            'last_payment_date'       => 'nullable|date',
            'cheque_name'             => 'nullable|string|max:255',
            'payment_mode'            => 'required|in:BEFTN,Cheque,bKash,Cash',
            'payment_account_details' => 'nullable|string',
            'bill_file'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks'                 => 'nullable|string',

            // Prepaid fields
            'last_recharge_amount'        => 'nullable|numeric|min:0',
            'last_recharge_date'          => 'nullable|date',
            'balance_after_last_recharge' => 'required_if:bill_type,prepaid|nullable|numeric|min:0',
            'last_balance'                => 'required_if:bill_type,prepaid|nullable|numeric|min:0',
            'recharge_amount'             => 'required_if:bill_type,prepaid|nullable|numeric|min:0',
            'per_day_consumption'         => 'required_if:bill_type,prepaid|nullable|numeric|min:0',
            'recharge_date'               => 'required_if:bill_type,prepaid|nullable|date',
            'is_consumption_edited'       => 'nullable',

            // Dedicated override fields
            'consumption_edit_remarks'    => 'nullable|string',
            'consumption_edit_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,eml,msg|max:5120',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('bill_type') === 'prepaid') {
                $isEdited = $request->input('is_consumption_edited') == '1' || $request->input('is_consumption_edited') === true;
                if ($isEdited) {
                    $hasRemarks = !empty(trim($request->input('consumption_edit_remarks', '')));
                    $hasFile = $request->hasFile('consumption_edit_attachment');
                    if (!$hasRemarks && !$hasFile) {
                        $validator->errors()->add('per_day_consumption', 'If per day consumption is manually edited, you must provide dedicated override remarks or upload an override proof attachment.');
                    }
                }
            }
        });

        $validated = $validator->validate();

        $meter = ElectricityMeter::with('building')->findOrFail($validated['meter_id']);
        
        // Convert YYYY-MM from calendar picker to Sep'26 format if needed
        if (!empty($validated['billing_month']) && strtotime($validated['billing_month'] . '-01')) {
            $validated['billing_month'] = date("M'y", strtotime($validated['billing_month'] . '-01'));
        }

        $validated['building_id'] = $meter->building_id;
        $validated['rio_id']      = $meter->building->rio_id ?? null;
        $validated['created_by']  = Auth::id();
        $validated['status']      = 'generated';
        $validated['requisition_no'] = ElectricityBill::generateRequisitionNo($validated['payment_mode']);

        if ($validated['bill_type'] === 'postpaid') {
            // Off-Peak calculations
            $prevOffpeak = (float) ($validated['previous_reading'] ?? 0);
            $currOffpeak = (float) ($validated['current_reading'] ?? 0);
            $validated['units_consumed'] = max(0, $currOffpeak - $prevOffpeak);
            $validated['rate_per_unit'] = (float) ($validated['rate_per_unit'] ?? 0);
            $validated['amount_offpeak'] = 0.00;

            // Peak calculations
            $prevPeak = (float) ($request->input('previous_peak_reading') ?? 0);
            $currPeak = (float) ($request->input('current_peak_reading') ?? 0);
            $validated['previous_peak_reading'] = $prevPeak;
            $validated['current_peak_reading'] = $currPeak;
            $validated['units_peak_consumed'] = max(0, $currPeak - $prevPeak);
            
            $validated['rate_peak_per_unit'] = 0.00;
            $validated['amount_peak'] = 0.00;

            // Totals from user input
            $validated['net_amount'] = (float) ($request->input('net_amount') ?? 0);
            $validated['vat_amount'] = (float) ($request->input('vat_amount') ?? 0);
            $validated['late_fee'] = (float) ($request->input('late_fee') ?? 0);
            $validated['meter_charge'] = (float) ($request->input('meter_charge') ?? 0);
            $validated['others_amount'] = (float) ($request->input('others_amount') ?? 0);
            $validated['total_amount'] = $validated['net_amount'] + $validated['vat_amount'] + $validated['late_fee'] + $validated['meter_charge'] + $validated['others_amount'];

            // Reset prepaid fields
            $validated['last_recharge_amount'] = 0;
            $validated['last_recharge_date'] = null;
            $validated['balance_after_last_recharge'] = 0;
            $validated['last_balance'] = 0;
            $validated['recharge_amount'] = 0;
            $validated['current_balance'] = 0;
            $validated['per_day_consumption'] = 0;
            $validated['recharge_date'] = null;
            $validated['is_consumption_edited'] = false;
            $validated['consumption_edit_remarks'] = null;
            $validated['consumption_edit_attachment'] = null;
        } else {
            // Reset postpaid fields
            $validated['previous_reading'] = 0;
            $validated['current_reading']  = 0;
            $validated['units_consumed']   = 0;
            $validated['rate_per_unit']    = 0;
            
            $validated['previous_peak_reading'] = 0;
            $validated['current_peak_reading']  = 0;
            $validated['units_peak_consumed']   = 0;
            $validated['rate_peak_per_unit']    = 0;
            $validated['amount_peak']           = 0;
            $validated['amount_offpeak']        = 0;
            $validated['late_fee']              = 0;
            $validated['meter_charge']          = 0;
            $validated['others_amount']         = 0;

            // Save prepaid fields
            $validated['last_recharge_amount'] = (float) ($request->input('last_recharge_amount') ?? 0);
            $validated['last_recharge_date'] = $request->filled('last_recharge_date') ? $request->input('last_recharge_date') : null;
            $validated['balance_after_last_recharge'] = (float) ($request->input('balance_after_last_recharge') ?? 0);
            $validated['last_balance'] = (float) ($request->input('last_balance') ?? 0);
            $validated['recharge_amount'] = (float) ($request->input('recharge_amount') ?? 0);
            $validated['current_balance'] = $validated['last_balance'] + $validated['recharge_amount'];
            $validated['recharge_date'] = $request->filled('recharge_date') ? $request->input('recharge_date') : null;
            $validated['is_consumption_edited'] = $request->input('is_consumption_edited') == '1';

            // Server-side calculation / validation of per_day_consumption
            if ($validated['is_consumption_edited']) {
                $validated['per_day_consumption'] = (float) ($request->input('per_day_consumption') ?? 0);
            } else {
                if ($validated['last_recharge_date'] && $validated['recharge_date']) {
                    $lastDate = new \DateTime($validated['last_recharge_date']);
                    $currDate = new \DateTime($validated['recharge_date']);
                    $days = $lastDate->diff($currDate)->days;
                    if ($days > 0) {
                        $consumed = $validated['balance_after_last_recharge'] - $validated['last_balance'];
                        $validated['per_day_consumption'] = max(0, $consumed) / $days;
                    } else {
                        $validated['per_day_consumption'] = 0;
                    }
                } else {
                    $validated['per_day_consumption'] = 0;
                }
            }

            // Derive prepaid financials
            $validated['net_amount'] = $validated['recharge_amount'];
            $validated['vat_amount'] = 0.00;
            $validated['total_amount'] = $validated['recharge_amount'];
            $validated['received_subcenter_date'] = null;
            $validated['last_payment_date'] = null;

            // Handle dedicated manual override proof fields
            $validated['consumption_edit_remarks'] = $request->input('consumption_edit_remarks');
            if ($request->hasFile('consumption_edit_attachment')) {
                $validated['consumption_edit_attachment'] = $request->file('consumption_edit_attachment')->store('electricity-bills/overrides', 'public');
            } else {
                $validated['consumption_edit_attachment'] = null;
            }
        }

        if ($request->hasFile('bill_file')) {
            $validated['bill_file_path'] = $request->file('bill_file')->store('electricity-bills', 'public');
        }

        $bill = ElectricityBill::create($validated);

        return redirect()->route('electricity.bills.show', $bill->id)
            ->with('success', 'Electricity requisition bill generated successfully: ' . $bill->requisition_no);
    }

    public function show(ElectricityBill $bill)
    {
        $bill->load(['meter.building.rio', 'meter.vendor', 'creator', 'payer']);

        $landOwnerName = null;
        if ($bill->building_id) {
            $agreement = \App\Models\Agreement::whereHas('floors', function ($q) use ($bill) {
                $q->where('building_id', $bill->building_id);
            })->with('vendor')->latest()->first();

            if ($agreement && $agreement->vendor) {
                $landOwnerName = $agreement->vendor->name;
            }
        }
        if (!$landOwnerName) {
            $landOwnerName = $bill->meter->vendor->name ?? $bill->meter->meter_owner ?? 'N/A';
        }

        $previousBill = ElectricityBill::where('meter_id', $bill->meter_id)
            ->where('id', '<', $bill->id)
            ->latest('id')
            ->first();

        return view('FacilitiesManagement.Electricity.Bills.show', compact('bill', 'landOwnerName', 'previousBill'));
    }

    public function printSheet(ElectricityBill $bill)
    {
        $bill->load(['meter.building.rio', 'meter.vendor', 'creator']);

        $landOwnerName = null;
        if ($bill->building_id) {
            $agreement = \App\Models\Agreement::whereHas('floors', function ($q) use ($bill) {
                $q->where('building_id', $bill->building_id);
            })->with('vendor')->latest()->first();

            if ($agreement && $agreement->vendor) {
                $landOwnerName = $agreement->vendor->name;
            }
        }
        if (!$landOwnerName) {
            $landOwnerName = $bill->meter->vendor->name ?? $bill->meter->meter_owner ?? 'N/A';
        }

        $previousBill = ElectricityBill::where('meter_id', $bill->meter_id)
            ->where('id', '<', $bill->id)
            ->latest('id')
            ->first();

        return view('FacilitiesManagement.Electricity.Bills.print', compact('bill', 'landOwnerName', 'previousBill'));
    }

    public function bulkPrint(Request $request)
    {
        $query = ElectricityBill::with(['meter', 'building.rio', 'creator', 'payer']);

        if ($request->filled('ids')) {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        } else {
            if ($request->filled('rio_id') && $request->rio_id !== 'all') {
                $query->where('rio_id', $request->rio_id);
            }
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
            if ($request->filled('bill_type') && $request->bill_type !== 'all') {
                $query->where('bill_type', $request->bill_type);
            }
            if ($request->filled('billing_month')) {
                $time = strtotime($request->billing_month . '-01');
                if ($time) {
                    $query->where('billing_month', date("M'y", $time));
                }
            }
            if ($request->filled('project_name') && $request->project_name !== 'all') {
                $query->where('project_name', $request->project_name);
            }
        }

        $bills = $query->latest()->get();

        if ($bills->isEmpty()) {
            return redirect()->back()->with('error', 'No bills found matching the selected criteria.');
        }

        return view('FacilitiesManagement.Electricity.Bills.bulk_print', compact('bills'));
    }

    public function markAsPaid(Request $request, ElectricityBill $bill)
    {
        $request->validate([
            'payment_date'      => 'required|date',
            'payment_reference' => 'required|string|max:100',
        ]);

        $bill->update([
            'status'            => 'paid',
            'paid_by'           => Auth::id(),
            'payment_date'      => $request->payment_date,
            'payment_reference' => $request->payment_reference,
        ]);

        return redirect()->back()->with('success', 'Payment recorded successfully for requisition ' . $bill->requisition_no);
    }
}
