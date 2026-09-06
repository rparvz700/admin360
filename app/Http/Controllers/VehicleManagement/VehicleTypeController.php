<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleType;
use App\Models\Vehicle;

class VehicleTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:vehicle-management|create-vehicle-type|edit-vehicle-type|delete-vehicle-type', ['only' => ['index', 'show', 'list']]);
        $this->middleware('permission:create-vehicle-type', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-vehicle-type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-vehicle-type', ['only' => ['destroy']]);
    }

    public function index()
    {
        $stats = [
            'total_types' => VehicleType::count(),
            'total_vehicles' => Vehicle::whereNotNull('vehicle_type_id')->count(),
            'most_common' => VehicleType::withCount('vehicles')->orderBy('vehicles_count', 'desc')->first(),
        ];

        return view('VehicleManagement.VehicleTypes.index', compact('stats'));
    }

    public function list(Request $request)
    {
        $query = VehicleType::withCount('vehicles');
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('type_name', 'like', "%$search%");
            });
        }

        $total = VehicleType::count();
        $filtered = $query->count();
        $types = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();

        $data = [];
        foreach ($types as $type) {
            $data[] = [
                'id' => $type->id,
                'type_name' => '<div class="fw-semibold text-dark"><i class="fa fa-tag me-2 text-primary"></i>' . e($type->type_name) . '</div>',
                'vehicles_count' => '<span class="badge bg-primary-light text-primary fs-xs fw-semibold"><i class="fa fa-car me-1"></i>' . $type->vehicles_count . ' Vehicles</span>',
                'actions' => view('VehicleManagement.VehicleTypes.partials.actions', compact('type'))->render(),
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
        return view('VehicleManagement.VehicleTypes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_name' => 'required|string|max:100|unique:vehicle_types,type_name',
        ]);
        VehicleType::create($validated);
        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type created successfully.');
    }

    public function show($id)
    {
        $type = VehicleType::withCount('vehicles')
            ->with(['vehicles' => function($q) {
                $q->latest()->limit(10);
            }])
            ->findOrFail($id);

        return view('VehicleManagement.VehicleTypes.show', compact('type'));
    }

    public function edit($id)
    {
        $type = VehicleType::findOrFail($id);
        return view('VehicleManagement.VehicleTypes.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = VehicleType::findOrFail($id);
        $validated = $request->validate([
            'type_name' => 'required|string|max:100|unique:vehicle_types,type_name,' . $id,
        ]);
        $type->update($validated);
        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type updated successfully.');
    }

    public function destroy($id)
    {
        $type = VehicleType::findOrFail($id);

        // Check if any vehicle uses this vehicle type
        if ($type->vehicles()->count() > 0) {
            return redirect()->route('vehicle-types.index')->with('error', 'Cannot delete vehicle type because it is currently assigned to ' . $type->vehicles()->count() . ' vehicle(s).');
        }

        $type->delete();
        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type deleted successfully.');
    }
}
