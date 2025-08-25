<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Models\PropertiesFloor;
use App\Models\Agreement;
use App\Models\PropertiesBuilding;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class FloorsController extends Controller
{
    /**
     * Display the specified floor.
     */
    public function show($id)
    {
        $floor = PropertiesFloor::with(['building', 'agreement'])->findOrFail($id);
        $building = $floor->building;
        $agreement = $floor->agreement;
        $rentBase = null;
        $rentIncrements = collect();
        $securityDeposits = collect();
        if ($agreement) {
            $rentBase = \App\Models\RentBase::where('agreement_id', $agreement->id)->first();
            $rentIncrements = \App\Models\RentIncrement::where('agreement_id', $agreement->id)->get();
            $securityDeposits = \App\Models\SecurityDeposit::where('agreement_id', $agreement->id)->get();
        }
        return view('FacilitiesManagement.Floors.show', compact('floor', 'building', 'agreement', 'rentBase', 'rentIncrements', 'securityDeposits'));
    }
    public function list(Request $request)
    {
        $query = PropertiesFloor::with(['building', 'agreement']);
        return DataTables::of($query)
            ->addColumn('building', function ($floor) {
                return $floor->building ? $floor->building->code : '';
            })
            ->addColumn('agreement', function ($floor) {
                return $floor->agreement ? $floor->agreement->agreement_ref_no : '';
            })
            ->addColumn('actions', function ($floor) {
                return view('FacilitiesManagement.Floors.partials.actions', compact('floor'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function index()
    {
        return view('FacilitiesManagement.Floors.index');
    }

    public function create()
    {
        $buildings = PropertiesBuilding::select('id', 'site_name')->get();
        $agreements = Agreement::select('id', 'agreement_ref_no')->get();
        return view('FacilitiesManagement.Floors.create', compact('buildings', 'agreements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:properties_building,id',
            'agreement_id' => 'nullable|exists:agreements,id',
            'owner_id' => 'nullable|exists:owners,id',
            'floor_label' => 'nullable|string|max:255',
            'floor_area_sft' => 'nullable|numeric',
            'premises_type' => 'nullable|string|max:255',
            'car_parking' => 'nullable|integer',
            'dg_space_sft' => 'nullable|numeric',
            'store_space_sft' => 'nullable|numeric',
            'project_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
        ]);
        PropertiesFloor::create($validated);
        return redirect()->route('floors.index')->with('success', 'Floor created successfully.');
    }

    public function edit($id)
    {
        $floor = PropertiesFloor::findOrFail($id);
        $buildings = PropertiesBuilding::all();
        $agreements = Agreement::all();
        return view('FacilitiesManagement.Floors.edit', compact('floor', 'buildings', 'agreements'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:properties_building,id',
            'agreement_id' => 'nullable|exists:agreements,id',
            'owner_id' => 'nullable|exists:owners,id',
            'floor_label' => 'nullable|string|max:255',
            'floor_area_sft' => 'nullable|numeric',
            'premises_type' => 'nullable|string|max:255',
            'car_parking' => 'nullable|integer',
            'dg_space_sft' => 'nullable|numeric',
            'store_space_sft' => 'nullable|numeric',
            'project_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
        ]);
        $floor = PropertiesFloor::findOrFail($id);
        $floor->update($validated);
        return redirect()->route('floors.index')->with('success', 'Floor updated successfully.');
    }

    public function destroy($id)
    {
        $floor = PropertiesFloor::findOrFail($id);
        $floor->delete();
        return redirect()->route('floors.index')->with('success', 'Floor deleted successfully.');
    }
}
