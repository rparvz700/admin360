<?php

namespace App\Http\Controllers\FacilitiesManagement\Electricity;

use App\Http\Controllers\Controller;
use App\Models\Rio;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rios = Rio::withCount(['buildings', 'users'])->orderBy('code');
            return DataTables::of($rios)
                ->addColumn('user_count', function ($rio) {
                    return '<span class="badge bg-info-light text-info fw-semibold"><i class="fa fa-users me-1"></i>' . $rio->users_count . ' Users</span>';
                })
                ->addColumn('building_count', function ($rio) {
                    return '<span class="badge bg-primary-light text-primary fw-semibold"><i class="fa fa-building me-1"></i>' . $rio->buildings_count . ' Sites</span>';
                })
                ->editColumn('is_active', function ($rio) {
                    return $rio->is_active
                        ? '<span class="badge bg-success-light text-success fw-semibold"><i class="fa fa-check me-1"></i>Active</span>'
                        : '<span class="badge bg-secondary-light text-secondary">Inactive</span>';
                })
                ->addColumn('actions', function ($rio) {
                    return '
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-alt-secondary edit-rio-btn" data-rio=\'' . json_encode($rio) . '\' title="Edit RIO">
                                <i class="fa fa-pencil-alt text-warning me-1"></i> Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-alt-secondary tag-user-btn" data-id="' . $rio->id . '" data-users=\'' . json_encode($rio->users->pluck('id')) . '\' title="Assign Users">
                                <i class="fa fa-user-plus text-info me-1"></i> Users
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['user_count', 'building_count', 'is_active', 'actions'])
                ->make(true);
        }

        $users = User::orderBy('name')->get();
        return view('FacilitiesManagement.Electricity.Rios.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:rios,code',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        Rio::create($validated);

        return redirect()->back()->with('success', 'RIO created successfully.');
    }

    public function update(Request $request, Rio $rio)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:rios,code,' . $rio->id,
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $rio->update($validated);

        return redirect()->back()->with('success', 'RIO updated successfully.');
    }

    public function assignUsers(Request $request, Rio $rio)
    {
        $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $rio->users()->sync($request->input('user_ids', []));

        return redirect()->back()->with('success', 'Users assigned to RIO successfully.');
    }
}
