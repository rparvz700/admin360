<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Helpers\Helpers;
use App\Models\PropertiesFloor;
use App\Models\Agreement;
use App\Models\PropertiesBuilding;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TableSetting;

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
        $query = PropertiesFloor::with(['building', 'agreement'])->orderBy('id', 'desc');
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('code', function ($floor) {
                return $floor->building ? $floor->building->code : '';
            })
            ->addColumn('building', function ($floor) {
                return $floor->building ? $floor->building->site_name : '';
            })
            ->addColumn('agreement', function ($floor) {
                return $floor->agreement ? $floor->agreement->agreement_ref_no : '';
            })
            ->addColumn('actions', function ($floor) {
                return view('FacilitiesManagement.Floors.partials.actions', compact('floor'))->render();
            })
            ->filterColumn('building', function ($query, $keyword) {
                $query->whereHas('building', function ($q) use ($keyword) {
                    $q->where('site_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('code', function ($query, $keyword) {
                $query->whereHas('building', function ($q) use ($keyword) {
                    $q->where('code', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('agreement', function ($query, $keyword) {
                $query->whereHas('agreement', function ($q) use ($keyword) {
                    $q->where('agreement_ref_no', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function index()
    {
        $globalSettings = TableSetting::where('table_identifier', 'floors_table')->first();
        $tableConfig = $globalSettings ? $globalSettings->settings : null;

        return view('FacilitiesManagement.Floors.index', compact('tableConfig'));
    }

    public function create()
    {
        $buildings = PropertiesBuilding::select('id', 'site_name')->get();
        $agreements = Agreement::select('id', 'agreement_ref_no')->get();
        $projects = Project::where('status', 1)->get();

        return view('FacilitiesManagement.Floors.create', compact('buildings', 'agreements', 'projects'));
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
            'project_id' => 'nullable',
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
        $projects = Project::where('status', 1)->get();

        return view('FacilitiesManagement.Floors.edit', compact('floor', 'buildings', 'agreements', 'projects'));
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
            'project_id' => 'nullable',
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


    // public function getHistory($id)
    // {
    //     $floor = PropertiesFloor::findOrFail($id);

    //     // Fetch all logs for this specific floor
    //     $activities = $floor->activities()->with('causer')->get();

    //     $history = $activities->map(function ($activity) {
    //         $oldValue = $activity->changes['old'] ?? [];
    //         $newValue = $activity->changes['attributes'] ?? [];

    //         $details = [];
    //         foreach ($newValue as $key => $val) {
    //             $oldVal = $oldValue[$key] ?? 'None';
                
    //             // Special logic for Agreement IDs to make them readable
    //             if ($key === 'agreement_id') {
    //                 $oldAg = Agreement::find($oldVal);
    //                 $newAg = Agreement::find($val);
                    
    //                 $oldVal = $oldAg ? $oldAg->agreement_ref_no : 'None';
    //                 $val = $newAg ? $newAg->agreement_ref_no : 'None';
    //                 $key = 'Agreement';
    //             }

    //             $details[] = [
    //                 'field' => ucfirst(str_replace('_', ' ', $key)),
    //                 'from'  => $oldVal,
    //                 'to'    => $val
    //             ];
    //         }

    //         return [
    //             'user' => $activity->causer->name ?? 'System',
    //             'date' => $activity->created_at->format('d M Y, h:i A'),
    //             'changes' => $details
    //         ];
    //     });

    //     return response()->json($history);
    // }


    public function getHistory($id)
    {
        $history = Helpers::getHistory(PropertiesFloor::class, $id);
        return $history;
    }
}
