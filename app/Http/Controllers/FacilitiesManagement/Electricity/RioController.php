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
                    $id = $rio->id;
                    $rioJson = htmlspecialchars(json_encode($rio), ENT_QUOTES, 'UTF-8');
                    $usersJson = htmlspecialchars(json_encode($rio->users->pluck('id')), ENT_QUOTES, 'UTF-8');

                    $html = '<div class="dropdown d-inline-block">';
                    $html .= '<button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="rioActions' . $id . '" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                    $html .= '<div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="rioActions' . $id . '">';
                    
                    $html .= '<a class="dropdown-item py-1 edit-rio-btn" href="javascript:void(0)" data-rio=\'' . $rioJson . '\'><i class="fa fa-pencil-alt text-warning me-2"></i> Edit RIO</a>';
                    $html .= '<a class="dropdown-item py-1 tag-user-btn" href="javascript:void(0)" data-id="' . $id . '" data-users=\'' . $usersJson . '\'><i class="fa fa-user-plus text-info me-2"></i> Assign Users</a>';
                    
                    $html .= '</div></div>';
                    return $html;
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
