<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehicleType;

class VehicleController extends Controller
{
    public function index()
    {
        return view('VehicleManagement.Vehicles.index');
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
                ;
            });
        }
        $total = Vehicle::count();
        $filtered = $query->count();
        $vehicles = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();
        $data = [];
        foreach ($vehicles as $vehicle) {
            $data[] = [
                'id' => $vehicle->id,
                'registration_number' => $vehicle->registration_number,
                'vehicle_type' => $vehicle->vehicleType->type_name ?? '',
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'manufacture_year' => $vehicle->manufacture_year,
                'status' => $vehicle->status,
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
            'engine_number' => 'nullable|unique:vehicles,engine_number',
            'chassis_number' => 'nullable|unique:vehicles,chassis_number',
            'use_purpose' => 'nullable',
            'use_company' => 'nullable',
            'isRented' => 'boolean',
            'purchase_price' => 'nullable|numeric',
            'purchase_date' => 'nullable|date',
            'status' => 'required',
        ]);
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
            'engine_number' => 'nullable|unique:vehicles,engine_number,' . $id,
            'chassis_number' => 'nullable|unique:vehicles,chassis_number,' . $id,
            'use_purpose' => 'nullable',
            'use_company' => 'nullable',
            'isRented' => 'boolean',
            'purchase_price' => 'nullable|numeric',
            'purchase_date' => 'nullable|date',
            'status' => 'required',
        ]);
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
