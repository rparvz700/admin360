<?php

namespace App\Http\Controllers\FacilitiesManagement\Electricity;

use App\Http\Controllers\Controller;
use App\Models\ElectricityMeter;
use App\Models\PropertiesBuilding;
use App\Models\PropertiesFloor;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ElectricityMeterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ElectricityMeter::with(['building.rio', 'floor', 'floors', 'vendor'])->latest();

            if ($request->has('meter_type') && $request->meter_type !== 'all' && $request->meter_type !== '') {
                $query->where('meter_type', $request->meter_type);
            }

            if ($request->has('is_active') && $request->is_active !== 'all' && $request->is_active !== '') {
                $query->where('is_active', $request->is_active);
            }

            return DataTables::of($query)
                ->editColumn('meter_number', function ($meter) {
                    return '<span class="fw-bold text-primary">' . e($meter->meter_number) . '</span>';
                })
                ->addColumn('site', function ($meter) {
                    $siteName = $meter->building->site_name ?? 'N/A';
                    $code     = $meter->building->code ?? $meter->building->site_code;
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
                ->addColumn('floors_list', function ($meter) {
                    if ($meter->floors && $meter->floors->count() > 0) {
                        return $meter->floors->pluck('floor_label')->implode(', ');
                    }
                    return $meter->floor->floor_label ?? 'N/A';
                })
                ->filterColumn('floors_list', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->where(function ($q) use ($lower) {
                        $q->whereHas('floor', function ($q2) use ($lower) {
                            $q2->whereRaw("LOWER(floor_label) LIKE ?", ["%{$lower}%"]);
                        })
                        ->orWhereHas('floors', function ($q2) use ($lower) {
                            $q2->whereRaw("LOWER(floor_label) LIKE ?", ["%{$lower}%"]);
                        });
                    });
                })
                ->addColumn('rio', function ($meter) {
                    return $meter->building->rio->name ?? 'N/A';
                })
                ->filterColumn('rio', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->whereHas('building.rio', function ($q) use ($lower) {
                        $q->whereRaw("LOWER(name) LIKE ?", ["%{$lower}%"]);
                    });
                })
                ->addColumn('meter_type_badge', function ($meter) {
                    return '<span class="badge bg-' . $meter->meter_type_badge . '">' . $meter->meter_type_label . '</span>';
                })
                ->editColumn('due_date_day', function ($meter) {
                    if (!$meter->due_date_day) return 'N/A';
                    return $meter->due_date_day . date('S', mktime(0, 0, 0, 1, $meter->due_date_day)) . ' of month';
                })
                ->addColumn('authority_display', function ($meter) {
                    return $meter->authority_name ?: ($meter->provider_name ?: 'N/A');
                })
                ->editColumn('payment_process', function ($meter) {
                    return $meter->payment_process ?: 'N/A';
                })
                ->editColumn('meter_owner', function ($meter) {
                    return $meter->meter_owner ?: 'N/A';
                })
                ->addColumn('assigned_to', function ($meter) {
                    if ($meter->vendor) return 'Vendor/Owner: ' . $meter->vendor->name;
                    return 'Direct Utility Provider';
                })
                ->filterColumn('assigned_to', function ($query, $keyword) {
                    $lower = strtolower($keyword);
                    $query->whereHas('vendor', function ($q) use ($lower) {
                        $q->whereRaw("LOWER(name) LIKE ?", ["%{$lower}%"]);
                    });
                })
                ->editColumn('is_active', function ($meter) {
                    return $meter->is_active
                        ? '<span class="badge bg-success-light text-success"><i class="fa fa-check me-1"></i>Active</span>'
                        : '<span class="badge bg-secondary-light text-secondary">Inactive</span>';
                })
                ->addColumn('actions', function ($meter) {
                    $id = $meter->id;
                    $html = '<div class="dropdown d-inline-block">';
                    $html .= '<button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="meterActions' . $id . '" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                    $html .= '<div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="meterActions' . $id . '">';
                    
                    $html .= '<a class="dropdown-item py-1" href="' . route('electricity.meters.edit', $id) . '"><i class="fa fa-pencil-alt text-warning me-2"></i> Edit Meter</a>';
                    
                    $html .= '</div></div>';
                    return $html;
                })
                ->rawColumns(['meter_number', 'site', 'meter_type_badge', 'is_active', 'actions'])
                ->make(true);
        }

        return view('FacilitiesManagement.Electricity.Meters.index');
    }

    public function create()
    {
        $buildings = PropertiesBuilding::with('rio')->orderBy('site_name')->get();
        $floors = PropertiesFloor::orderBy('floor_label')->get();
        $vendors = Vendor::orderBy('name')->get();

        return view('FacilitiesManagement.Electricity.Meters.create', compact('buildings', 'floors', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meter_number'         => 'required|string|max:100',
            'meter_type'           => 'required|in:postpaid_main,postpaid_sub,prepaid',
            'authority_name'       => 'nullable|string|max:100',
            'provider_name'        => 'nullable|string|max:100',
            'payment_process'      => 'nullable|string|in:bKash,Bank Challan,BEFTN',
            'meter_owner'          => 'nullable|string|in:Bangladesh Railway,House Owner,SComm',
            'building_id'          => 'required|exists:properties_building,id',
            'floor_ids'            => 'nullable|array',
            'floor_ids.*'          => 'exists:properties_floors,id',
            'vendor_id'            => 'nullable|exists:vendors,id',
            'consumer_no'          => 'nullable|string|max:100',
            'due_date_day'         => 'nullable|integer|between:1,31',
            'sanctioned_load_kw'   => 'nullable|numeric|min:0',
            'meter_location_notes' => 'nullable|string|max:255',
            'is_active'            => 'boolean',
        ]);

        if (!empty($validated['authority_name'])) {
            $validated['provider_name'] = $validated['authority_name'];
        }

        $floorIds = array_filter($validated['floor_ids'] ?? []);
        $validated['floor_id'] = $floorIds[0] ?? null;

        $meter = ElectricityMeter::create($validated);
        $meter->floors()->sync($floorIds);

        return redirect()->route('electricity.meters.index')->with('success', 'Electricity meter created successfully.');
    }

    public function edit(ElectricityMeter $meter)
    {
        $meter->load(['floors']);
        $buildings = PropertiesBuilding::with('rio')->orderBy('site_name')->get();
        $floors = PropertiesFloor::orderBy('floor_label')->get();
        $vendors = Vendor::orderBy('name')->get();

        return view('FacilitiesManagement.Electricity.Meters.edit', compact('meter', 'buildings', 'floors', 'vendors'));
    }

    public function update(Request $request, ElectricityMeter $meter)
    {
        $validated = $request->validate([
            'meter_number'         => 'required|string|max:100',
            'meter_type'           => 'required|in:postpaid_main,postpaid_sub,prepaid',
            'authority_name'       => 'nullable|string|max:100',
            'provider_name'        => 'nullable|string|max:100',
            'payment_process'      => 'nullable|string|in:bKash,Bank Challan,BEFTN',
            'meter_owner'          => 'nullable|string|in:Bangladesh Railway,House Owner,SComm',
            'building_id'          => 'required|exists:properties_building,id',
            'floor_ids'            => 'nullable|array',
            'floor_ids.*'          => 'exists:properties_floors,id',
            'vendor_id'            => 'nullable|exists:vendors,id',
            'consumer_no'          => 'nullable|string|max:100',
            'due_date_day'         => 'nullable|integer|between:1,31',
            'sanctioned_load_kw'   => 'nullable|numeric|min:0',
            'meter_location_notes' => 'nullable|string|max:255',
            'is_active'            => 'boolean',
        ]);

        if (!empty($validated['authority_name'])) {
            $validated['provider_name'] = $validated['authority_name'];
        }

        $floorIds = array_filter($validated['floor_ids'] ?? []);
        $validated['floor_id'] = $floorIds[0] ?? null;

        $meter->update($validated);
        $meter->floors()->sync($floorIds);

        return redirect()->route('electricity.meters.index')->with('success', 'Electricity meter updated successfully.');
    }

    public function getAgreementVendor($building_id)
    {
        $agreement = \App\Models\Agreement::whereHas('floors', function ($q) use ($building_id) {
            $q->where('building_id', $building_id);
        })->with('vendor')->latest()->first();

        return response()->json([
            'vendor_id' => $agreement && $agreement->vendor ? $agreement->vendor->id : null
        ]);
    }
}
