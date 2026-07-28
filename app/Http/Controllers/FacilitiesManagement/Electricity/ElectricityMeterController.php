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
            $meters = ElectricityMeter::with(['building.rio', 'floor', 'vendor'])->latest();

            return DataTables::of($meters)
                ->editColumn('meter_number', function ($meter) {
                    return '<span class="fw-bold text-primary">' . e($meter->meter_number) . '</span>';
                })
                ->addColumn('site', function ($meter) {
                    $code = $meter->building->code ?? $meter->building->site_code;
                    return ($meter->building->site_name ?? 'N/A') . ($code ? ' (' . $code . ')' : '');
                })
                ->addColumn('rio', function ($meter) {
                    return $meter->building->rio->name ?? 'N/A';
                })
                ->addColumn('meter_type_badge', function ($meter) {
                    return '<span class="badge bg-' . $meter->meter_type_badge . '">' . $meter->meter_type_label . '</span>';
                })
                ->addColumn('assigned_to', function ($meter) {
                    if ($meter->vendor) return 'Vendor/Owner: ' . $meter->vendor->name;
                    return 'Direct Utility Provider';
                })
                ->editColumn('is_active', function ($meter) {
                    return $meter->is_active
                        ? '<span class="badge bg-success-light text-success"><i class="fa fa-check me-1"></i>Active</span>'
                        : '<span class="badge bg-secondary-light text-secondary">Inactive</span>';
                })
                ->addColumn('actions', function ($meter) {
                    return '
                        <div class="btn-group text-nowrap">
                            <a href="' . route('electricity.meters.edit', $meter->id) . '" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Edit Meter">
                                <i class="fa fa-pencil-alt text-warning me-1"></i> Edit
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['meter_number', 'meter_type_badge', 'is_active', 'actions'])
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
            'provider_name'        => 'nullable|string|max:100',
            'building_id'          => 'required|exists:properties_building,id',
            'floor_id'             => 'nullable|exists:properties_floors,id',
            'vendor_id'            => 'nullable|exists:vendors,id',
            'consumer_no'          => 'nullable|string|max:100',
            'sanctioned_load_kw'   => 'nullable|numeric|min:0',
            'meter_location_notes' => 'nullable|string|max:255',
            'is_active'            => 'boolean',
        ]);

        ElectricityMeter::create($validated);

        return redirect()->route('electricity.meters.index')->with('success', 'Electricity meter created successfully.');
    }

    public function edit(ElectricityMeter $meter)
    {
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
            'provider_name'        => 'nullable|string|max:100',
            'building_id'          => 'required|exists:properties_building,id',
            'floor_id'             => 'nullable|exists:properties_floors,id',
            'vendor_id'            => 'nullable|exists:vendors,id',
            'consumer_no'          => 'nullable|string|max:100',
            'sanctioned_load_kw'   => 'nullable|numeric|min:0',
            'meter_location_notes' => 'nullable|string|max:255',
            'is_active'            => 'boolean',
        ]);

        $meter->update($validated);

        return redirect()->route('electricity.meters.index')->with('success', 'Electricity meter updated successfully.');
    }
}
