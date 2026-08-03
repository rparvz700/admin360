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
                $query->where('billing_month', $request->billing_month);
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
        return view('FacilitiesManagement.Electricity.Bills.index', compact('rios'));
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
        $prevReading = $this->electricityService->getPreviousReading($meterId);
        $meter = ElectricityMeter::with('building.rio')->find($meterId);

        return response()->json([
            'previous_reading' => $prevReading,
            'meter' => $meter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meter_id'                => 'required|exists:electricity_meters,id',
            'bill_type'               => 'required|in:postpaid,prepaid',
            'project_name'            => 'required|string|max:100',
            'billing_month'           => 'required|string|max:20',
            'previous_reading'        => 'nullable|numeric|min:0',
            'current_reading'         => 'nullable|numeric|min:0',
            'units_consumed'          => 'nullable|numeric|min:0',
            'rate_per_unit'           => 'nullable|numeric|min:0',
            'net_amount'              => 'required|numeric|min:0',
            'vat_amount'              => 'nullable|numeric|min:0',
            'total_amount'            => 'required|numeric|min:0',
            'received_subcenter_date' => 'nullable|date',
            'last_payment_date'       => 'nullable|date',
            'cheque_name'             => 'nullable|string|max:255',
            'payment_mode'            => 'required|in:BEFTN,Cheque,bKash,Cash',
            'payment_account_details' => 'nullable|string',
            'bill_file'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks'                 => 'nullable|string',
        ]);

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
            $prev = (float) ($validated['previous_reading'] ?? 0);
            $curr = (float) ($validated['current_reading'] ?? 0);
            $validated['units_consumed'] = max(0, $curr - $prev);
        } else {
            $validated['previous_reading'] = 0;
            $validated['current_reading']  = 0;
            $validated['units_consumed']   = 0;
            $validated['rate_per_unit']    = 0;
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
