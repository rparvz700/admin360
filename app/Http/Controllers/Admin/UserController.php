<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Department;
use App\Models\Subcenter;
use App\Models\TableSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-user|edit-user|delete-user', ['only' => ['index','show']]);
        $this->middleware('permission:create-user', ['only' => ['create','store']]);
        $this->middleware('permission:edit-user', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-user', ['only' => ['destroy']]);
    }


    public function index()
    {
        $activeMenu = "users";
        $listRoute = route('userList');

        $globalSettings = TableSetting::where('table_identifier', 'users_table')->first();
        $tableConfig = $globalSettings ? $globalSettings->settings : null;

        return view('Admin.Users.index', compact('activeMenu', 'listRoute', 'tableConfig'));
    }

    public function userList()
    {
        $model = User::query();
        return DataTables::of($model)
            ->addIndexColumn() // This creates the 'DT_RowIndex' column
            ->editColumn('actions', function ($row) {
                $buttons = '';
                if (auth()->user()->can('edit-user') && !$row->hasRole('Super Admin')) {
                    $buttons .= '<a href="' . route('users.edit', $row->id) . '" style="margin-right: 3px;" class="btn btn-sm btn-primary p-1 py-0"><i class="fa fa-pen-to-square"></i></a>';
                }
                if (auth()->user()->can('delete-user') && Auth::id() != $row->id && !$row->hasRole('Super Admin')) {
                    $buttons .= '<form id="deleteForm' . $row->id . '" action="' . route('users.destroy', $row->id) . '" method="post" style="display:inline;">
                        ' . csrf_field() . '
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" class="btn btn-danger btn-sm p-1 py-0 delete-button" data-user-id="' . $row->id . '"><i class="fa fa-trash-can"></i></button>
                    </form>';
                }
                return '<div class="d-flex">' . $buttons . '</div>';
            })
            ->editColumn('roles', function ($row) {
                return $row->getRoleNames()->first() ?? '';
            })
            ->editColumn('status', function ($row) {
                return '<span class="badge bg-' . ($row->status == 1 ? 'success' : 'danger') . '">' . ($row->status == 1 ? 'Active' : 'Inactive') . '</span>';
            })
            ->rawColumns(['actions', 'roles', 'status'])
            ->make(true);
    }


    public function create()
    {
        $activeMenu = "users";
        // $departments = Department::where('status', 1)->get();
        // $subcenters = Subcenter::where('status', 1)->get();
        $roles = Role::get();
        return view('Admin.Users.create', compact('activeMenu', 'roles'));
    }


    public function store(StoreUserRequest $request)
    {
        $data = [
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            // "phone" => $request->phone,
            // "hr_id" => $request->hr_id,
            // "designation" => $request->designation,
            // "department_id" => $request->department,
            // "subcenter_id" => $request->subcenter,
            "status" => $request->status,
            "created_by" => Auth::user()->id
        ];

        $save = User::create($data);

        if($save){
            $save->assignRole($request->role);
            return redirect()->route('users.index')
                ->withSuccess('New user is added successfully.');
        }else{
            return redirect()->route('users.index')
                ->withSuccess('Something went wrong to add new user.');
        }
    }



    public function edit(User $user)
    {
        $activeMenu = "users";
        // $departments = Department::where('status', 1)->get();
        // $subcenters = Subcenter::where('status', 1)->get();
        $roles = Role::get();
        return view('Admin.Users.edit', compact('user', 'roles', 'activeMenu'));
    }


    public function update(User $user, UpdateUserRequest $request)
    {
        $data = [
            "name" => $request->name,
            // "phone" => $request->phone,
            // "hr_id" => $request->hr_id,
            // "designation" => $request->designation,
            // "department_id" => $request->department,
            // "subcenter_id" => $request->subcenter,
            "status" => $request->status,
            // "last_modified_by" => Auth::user()->id
        ];

        $update = $user->update($data);

        if($update){
            $user->syncRoles($request->role);
            return redirect()->back()
                    ->withSuccess('User is updated successfully.');
        }else{
            return redirect()->back()
                    ->withError('Something went wrong to update user');
        }
    }


    public function destroy(User $user)
    {
        $delete = $user->delete();

        if($delete){
        return redirect()->route('users.index')
                ->withSuccess('User is deleted successfully.');
        }else{
            return redirect()->route('users.index')
                ->withSuccess('Something went wrong to delete user');
        }
    }


}
