<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Models\PropertiesBuilding;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

use App\Http\Controllers\Controller;

class BuildingsController extends Controller
{
    public function list(Request $request)
    {
        $query = PropertiesBuilding::query();
        return DataTables::of($query)
            ->addColumn('actions', function ($building) {
                return view('FacilitiesManagement.Buildings.partials.actions', compact('building'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    public function index()
    {
        $buildings = PropertiesBuilding::all();
        return view('FacilitiesManagement.Buildings.index', compact('buildings'));
    }

    public function create()
    {
        return view('FacilitiesManagement.Buildings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'upazila' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ]);
        $building = PropertiesBuilding::create($validated);
        return redirect()->route('buildings.index')->with('success', 'Building created successfully.');
    }

    public function show($id)
    {
        $building = PropertiesBuilding::findOrFail($id);
        return view('Admin.Buildings.show', compact('building'));
    }

    public function edit($id)
    {
        $building = PropertiesBuilding::findOrFail($id);
        return view('Admin.Buildings.edit', compact('building'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'upazila' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ]);
        $building = PropertiesBuilding::findOrFail($id);
        $building->update($validated);
        return redirect()->route('buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy($id)
    {
        $building = PropertiesBuilding::findOrFail($id);
        $building->delete();
        return redirect()->route('buildings.index')->with('success', 'Building deleted successfully.');
    }
}
