<?php

namespace App\Http\Controllers\FacilitiesManagement\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Project::query();
            return \Yajra\DataTables\DataTables::of($query)
                ->editColumn('status', function ($row) {
                    $badge = '<span class="badge bg-' . ($row->status == 1 ? 'success' : 'danger') . '">' . (($row->status == 1) ? 'Active' : 'Inactive') . '</span>';
                    return $badge;
                })
                ->addColumn('actions', function ($row) {
                    return view('FacilitiesManagement.Projects.partials.actions', compact('row'))->render();
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }
        return view('FacilitiesManagement.Projects.index');
    }


    public function create()
    {
        return view('FacilitiesManagement.Projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:projects,name',
            'description' => 'nullable',
            'status' => 'required',
        ]);
        Project::create($validated);
        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }


    public function edit($id)
    {
        $project = Project::findOrFail($id);
        return view('FacilitiesManagement.Projects.edit', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|unique:projects,name,' . $id,
            'description' => 'nullable',
            'status' => 'required',
        ]);
        $project = Project::findOrFail($id);
        $project->update($validated);
        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
