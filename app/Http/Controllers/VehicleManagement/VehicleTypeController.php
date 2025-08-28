<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleType;

class VehicleTypeController extends Controller
{
    public function index()
    {
        return view('VehicleManagement.VehicleTypes.index');
    }

    public function list(Request $request)
    {
        $query = VehicleType::query();
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('type_name', 'like', "%$search%")
                ;
            });
        }
        $total = VehicleType::count();
        $filtered = $query->count();
        $types = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();
        $data = [];
        foreach ($types as $type) {
            $data[] = [
                'id' => $type->id,
                'type_name' => $type->type_name,
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
            'type_name' => 'required|unique:vehicle_types,type_name',
        ]);
        VehicleType::create($validated);
        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type created successfully.');
    }

    public function show($id)
    {
        $type = VehicleType::findOrFail($id);
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
            'type_name' => 'required|unique:vehicle_types,type_name,' . $id,
        ]);
        $type->update($validated);
        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type updated successfully.');
    }

    public function destroy($id)
    {
        $type = VehicleType::findOrFail($id);
        $type->delete();
        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type deleted successfully.');
    }
}
