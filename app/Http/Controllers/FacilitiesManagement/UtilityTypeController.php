<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Http\Controllers\Controller;
use App\Models\UtilityType;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UtilityTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-utility-type|edit-utility-type|delete-utility-type', ['only' => ['index', 'show', 'list']]);
        $this->middleware('permission:create-utility-type', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-utility-type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-utility-type', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = UtilityType::orderBy('id', 'desc')->get();
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('is_active', function ($row) {
                    return $row->is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    $buttons = '<div class="btn-group">';
                    if (auth()->user()->can('edit-utility-type')) {
                        $buttons .= '<a href="' . route('utility-types.edit', $row->id) . '" class="btn btn-sm btn-alt-primary" title="Edit">
                                <i class="fa fa-pencil-alt"></i>
                            </a>';
                    }
                    if (auth()->user()->can('delete-utility-type')) {
                        $buttons .= '<form action="' . route('utility-types.destroy', $row->id) . '" method="POST" style="display:inline-block;" onsubmit="return confirm(\'Are you sure you want to delete this utility type?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-alt-danger" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>';
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['is_active', 'actions'])
                ->make(true);
        }
        return view('FacilitiesManagement.UtilityTypes.index');
    }

    public function create()
    {
        return view('FacilitiesManagement.UtilityTypes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:utility_types,name',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        UtilityType::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('utility-types.index')->with('success', 'Utility type created successfully.');
    }

    public function edit($id)
    {
        $utilityType = UtilityType::findOrFail($id);
        return view('FacilitiesManagement.UtilityTypes.edit', compact('utilityType'));
    }

    public function update(Request $request, $id)
    {
        $utilityType = UtilityType::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:utility_types,name,' . $utilityType->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $utilityType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('utility-types.index')->with('success', 'Utility type updated successfully.');
    }

    public function destroy($id)
    {
        $utilityType = UtilityType::findOrFail($id);
        $utilityType->delete();

        return redirect()->route('utility-types.index')->with('success', 'Utility type deleted successfully.');
    }
}
