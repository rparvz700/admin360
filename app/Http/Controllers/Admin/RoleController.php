<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-role|edit-role|delete-role', ['only' => ['index','show']]);
        $this->middleware('permission:create-role', ['only' => ['create','store']]);
        $this->middleware('permission:edit-role', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-role', ['only' => ['destroy']]);
    }


    public function index()
    {
        $activeMenu = "roles";
        $roles  = Role::orderBy('id', 'desc')->get();

        return view('Admin.Roles.index', compact('activeMenu', 'roles'));
    }


    public function create()
    {
        $activeMenu = "roles";
        $permissions = Permission::orderBy('id', 'desc')->get();

        return view('Admin.Roles.create', compact('activeMenu', 'permissions'));
    }


    public function store(StoreRoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);

        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();

        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')
                ->withSuccess('New role is added successfully.');
    }


    public function edit(Role $role)
    {
        $activeMenu = "roles";

        if($role->name=='Super Admin'){
            abort(403, 'SUPER ADMIN ROLE CAN NOT BE EDITED');
        }

        $permissions = Permission::orderBy('id', 'DESC')->get();

        $rolePermissions = DB::table("role_has_permissions")->where("role_id",$role->id)
            ->pluck('permission_id')
            ->all();

        return view('Admin.roles.edit', compact('role', 'permissions', 'rolePermissions', 'activeMenu'));
    }


    public function update(UpdateRoleRequest $request, Role $role)
    {
        $roleName = $request->only('name');

        $role->update($roleName);

        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();

        $role->syncPermissions($permissions);

        return redirect()->back()
                ->withSuccess('Role is updated successfully.');
    }


    public function destroy(Role $role)
    {
        if($role->name=='Super Admin'){
            abort(403, 'SUPER ADMIN ROLE CAN NOT BE DELETED');
        }
        if(auth()->user()->hasRole($role->name)){
            abort(403, 'CAN NOT DELETE SELF ASSIGNED ROLE');
        }
        $role->delete();
        return redirect()->route('roles.index')
                ->withSuccess('Role is deleted successfully.');
    }
}
