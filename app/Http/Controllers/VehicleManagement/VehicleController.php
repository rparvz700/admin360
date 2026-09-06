<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehicleType;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:vehicle-management|create-vehicle|edit-vehicle|delete-vehicle', ['only' => ['index', 'show', 'list']]);
        $this->middleware('permission:create-vehicle', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-vehicle', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-vehicle', ['only' => ['destroy']]);
    }

    public function index()
    {
        $stats = [
            'total'   => Vehicle::count(),
            'active'  => Vehicle::where('status', 'active')->count(),
            'owned'   => Vehicle::where('isRented', false)->count(),
            'rented'  => Vehicle::where('isRented', true)->count(),
        ];
        return view('VehicleManagement.Vehicles.index', compact('stats'));
    }

    public function list(Request $request)
    {
        $query = Vehicle::with('vehicleType');
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%$search%")
                  ->orWhere('brand', 'like', "%$search%")
                  ->orWhere('model', 'like', "%$search%")
                  ->orWhere('engine_number', 'like', "%$search%")
                  ->orWhere('chassis_number', 'like', "%$search%")
                  ->orWhere('engine_cc', 'like', "%$search%")
                ;
            });
        }
        $total = Vehicle::count();
        $filtered = $query->count();
        $vehicles = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();
        $data = [];
        foreach ($vehicles as $vehicle) {
            $statusBadge = match($vehicle->status) {
                'active'   => '<span class="badge bg-success">Active</span>',
                'inactive' => '<span class="badge bg-warning">Inactive</span>',
                'scrapped' => '<span class="badge bg-danger">Scrapped</span>',
                default    => '<span class="badge bg-secondary">' . e(ucfirst($vehicle->status)) . '</span>',
            };

            $ownershipBadge = $vehicle->isRented
                ? '<span class="badge bg-info-light text-info"><i class="fa fa-handshake me-1"></i>Rented</span>'
                : '<span class="badge bg-primary-light text-primary"><i class="fa fa-shield-alt me-1"></i>Owned</span>';

            $data[] = [
                'id' => $vehicle->id,
                'registration_number' => '<span class="fw-semibold text-dark"><i class="fa fa-car me-1 text-muted"></i>' . e($vehicle->registration_number) . '</span>',
                'vehicle_type' => '<span class="badge bg-secondary">' . e($vehicle->vehicleType->type_name ?? 'N/A') . '</span>',
                'brand' => e($vehicle->brand ?? '-'),
                'model' => e($vehicle->model ?? '-'),
                'engine_cc' => $vehicle->engine_cc ? number_format($vehicle->engine_cc) . ' cc' : '-',
                'manufacture_year' => $vehicle->manufacture_year ?? '-',
                'ownership' => $ownershipBadge,
                'status' => $statusBadge,
                'actions' => view('VehicleManagement.Vehicles.partials.actions', compact('vehicle'))->render(),
            ];
        }
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    public function create()
    {
        $vehicleTypes = VehicleType::all();
        return view('VehicleManagement.Vehicles.create', compact('vehicleTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'registration_number' => 'required|unique:vehicles,registration_number',
            'brand' => 'nullable',
            'model' => 'nullable',
            'manufacture_year' => 'nullable|digits:4|integer',
            'color' => 'nullable',
            'seating_capacity' => 'nullable|integer',
            'engine_cc' => 'nullable|integer',
            'cc' => 'nullable|integer',
            'engine_number' => 'nullable|unique:vehicles,engine_number',
            'chassis_number' => 'nullable|unique:vehicles,chassis_number',
            'use_purpose' => 'nullable',
            'use_company' => 'nullable',
            'isRented' => 'boolean',
            'purchase_price' => 'nullable|numeric',
            'purchase_date' => 'nullable|date',
            'status' => 'required',
        ]);
        if (isset($validated['cc']) && !isset($validated['engine_cc'])) {
            $validated['engine_cc'] = $validated['cc'];
        }
        unset($validated['cc']);
        Vehicle::create($validated);
        return redirect()->route('vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function show($id)
    {
        $vehicle = Vehicle::with('vehicleType')->findOrFail($id);
        return view('VehicleManagement.Vehicles.show', compact('vehicle'));
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicleTypes = VehicleType::all();
        return view('VehicleManagement.Vehicles.edit', compact('vehicle', 'vehicleTypes'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'registration_number' => 'required|unique:vehicles,registration_number,' . $id,
            'brand' => 'nullable',
            'model' => 'nullable',
            'manufacture_year' => 'nullable|digits:4|integer',
            'color' => 'nullable',
            'seating_capacity' => 'nullable|integer',
            'engine_cc' => 'nullable|integer',
            'cc' => 'nullable|integer',
            'engine_number' => 'nullable|unique:vehicles,engine_number,' . $id,
            'chassis_number' => 'nullable|unique:vehicles,chassis_number,' . $id,
            'use_purpose' => 'nullable',
            'use_company' => 'nullable',
            'isRented' => 'boolean',
            'purchase_price' => 'nullable|numeric',
            'purchase_date' => 'nullable|date',
            'status' => 'required',
        ]);
        if (isset($validated['cc']) && !isset($validated['engine_cc'])) {
            $validated['engine_cc'] = $validated['cc'];
        }
        unset($validated['cc']);
        $vehicle->update($validated);
        return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}
