<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subcenter\StoreSubcenterRequest;
use App\Http\Requests\Subcenter\UpdateSubcenterRequest;
use App\Models\Department;
use App\Models\Subcenter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SubcenterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-subcenter|edit-subcenter|delete-subcenter', ['only' => ['index','show']]);
        $this->middleware('permission:create-subcenter', ['only' => ['create','store']]);
        $this->middleware('permission:edit-subcenter', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-subcenter', ['only' => ['destroy']]);
    }


    public function index()
    {
        $activeMenu = "subcenters";
        $listRoute = route('subcenterList');
        return view('Admin.Subcenters.index', compact('activeMenu', 'listRoute'));
    }

    public function subcenterList()
    {
        $model = Subcenter::with('department', 'subHead');
        $datatable = DataTables::of($model);
        $datatable
            ->editColumn('actions', function ($row) {
                $buttons = '';
                if (auth()->user()->can('edit-subcenter')) {
                    $buttons .= '<a href="' . route('subcenters.edit', $row->id) . '"
                        style="margin-right: 3px;" class="btn btn-sm btn-primary p-1 py-0">
                        <i class="fa fa-pen-to-square"></i>
                    </a>';
                }

                if (auth()->user()->can('delete-subcenter')) {
                    $buttons .= '<form id="deleteForm' . $row->id . '" action="' . route('subcenters.destroy', $row->id) . '" method="post">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" class="btn btn-danger btn-sm p-1 py-0 delete-button" data-subcenter-id="' . $row->id . '">
                        <i class="fa fa-trash-can"></i>
                    </button>
                    </form>';
                }

                return '<div class="d-flex" style="justify-content: start;">' . $buttons . '</div>';
            })
            ->editColumn('department_id', function ($row) {
                return (!empty($row->department_id) ? $row->department->name : '');
            })
            ->editColumn('head_id', function ($row) {
                return (!empty($row->head_id) ? $row->SubHead->name : '');
            })
            ->editColumn('status', function ($row) {
                // return (($row->status == 1) ? 'Active' : 'Inactive');
                $badge = '<span class="badge bg-' . ($row->status == 1 ? 'success' : 'danger') . '">' . (($row->status == 1) ? 'Active' : 'Inactive') . '</span>';
                return $badge;
            })
            ->rawColumns(['actions', 'department_id', 'head_id', 'status']);

        return $datatable->toJson();
    }


    public function create()
    {
        $activeMenu = "subcenters";
        $users = User::where('status', 1)->get();
        $departments = Department::where('status', 1)->get();
        return view('Admin.Subcenters.create', compact('activeMenu', 'users', 'departments'));
    }


    public function store(StoreSubcenterRequest $request)
    {
        $data = [
            "name" => $request->name,
            "department_id" => $request->region,
            "head_id" => $request->hod,
            "status" => $request->status,
            "added_by" => Auth::user()->id
        ];

        $save = Subcenter::create($data);

        if($save){
            return redirect()->route('subcenters.index')
                ->withSuccess('New subcenter is added successfully.');
        }else{
            return redirect()->route('subcenters.index')
                ->withSuccess('Something went wrong to add new subcenter.');
        }
    }


    public function edit(Subcenter $subcenter)
    {
        $activeMenu = "subcenters";
        $users = User::where('status', 1)->get();
        $departments = Department::where('status', 1)->get();
        return view('Admin.Subcenters.edit', compact('users', 'departments', 'subcenter', 'activeMenu'));
    }


    public function update(Subcenter $subcenter, UpdateSubcenterRequest $request)
    {
        $data = [
            "name" => $request->name,
            "department_id" => $request->region,
            "head_id" => $request->hod,
            "status" => $request->status,
            "last_modified_by" => Auth::user()->id
        ];

        $update = $subcenter->update($data);

        if($update){
            return redirect()->back()
                    ->withSuccess('Subcenter is updated successfully.');
        }else{
            return redirect()->back()
                    ->withError('Something went wrong to update subcenter');
        }
    }


    public function destroy(Subcenter $subcenter)
    {
        $delete = $subcenter->delete();

        if($delete){
        return redirect()->route('subcenters.index')
                ->withSuccess('Subcenter is deleted successfully.');
        }else{
            return redirect()->route('subcenters.index')
                ->withSuccess('Something went wrong to delete subcenter');
        }
    }
}
