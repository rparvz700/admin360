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
        $permissions = Permission::all();
        $groupedPermissions = $this->groupPermissions($permissions);

        return view('Admin.Roles.create', compact('activeMenu', 'groupedPermissions'));
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

        $permissions = Permission::all();
        $groupedPermissions = $this->groupPermissions($permissions);

        $rolePermissions = DB::table("role_has_permissions")->where("role_id",$role->id)
            ->pluck('permission_id')
            ->all();

        return view('Admin.Roles.edit', compact('role', 'groupedPermissions', 'rolePermissions', 'activeMenu'));
    }

    private function groupPermissions($permissions)
    {
        $groups = [
            'System Settings' => [],
            'Property Management' => [],
            'Asset Management' => [],
            'Vehicle Management' => [],
            'Vehicle Maintenance Management' => [],
            'Generic Document Management' => [],
            'Ticket Management' => [],
            'Invoice Management' => [],
            'Other Permissions' => [],
        ];

        $mapping = [
            // System Settings
            'create-role' => 'System Settings', 'edit-role' => 'System Settings', 'delete-role' => 'System Settings',
            'create-user' => 'System Settings', 'edit-user' => 'System Settings', 'delete-user' => 'System Settings',

            // Property Management
            'property-wizard' => 'Property Management',
            'create-building' => 'Property Management', 'edit-building' => 'Property Management', 'delete-building' => 'Property Management',
            'create-floor' => 'Property Management', 'edit-floor' => 'Property Management', 'delete-floor' => 'Property Management',
            'create-agreement' => 'Property Management', 'edit-agreement' => 'Property Management', 'delete-agreement' => 'Property Management',
            'create-rent' => 'Property Management', 'edit-rent' => 'Property Management', 'delete-rent' => 'Property Management',
            'create-utility-type' => 'Property Management', 'edit-utility-type' => 'Property Management', 'delete-utility-type' => 'Property Management',

            // Asset Management
            'asset-management' => 'Asset Management',
            'create-asset' => 'Asset Management', 'edit-asset' => 'Asset Management', 'delete-asset' => 'Asset Management',
            'create-project' => 'Asset Management', 'edit-project' => 'Asset Management', 'delete-project' => 'Asset Management',
            'create-asset-category' => 'Asset Management', 'edit-asset-category' => 'Asset Management', 'delete-asset-category' => 'Asset Management',
            'create-asset-attribute' => 'Asset Management', 'edit-asset-attribute' => 'Asset Management', 'delete-asset-attribute' => 'Asset Management',

            // Vehicle Management
            'vehicle-management' => 'Vehicle Management',
            'create-driver' => 'Vehicle Management', 'edit-driver' => 'Vehicle Management', 'delete-driver' => 'Vehicle Management',
            'create-vehicle' => 'Vehicle Management', 'edit-vehicle' => 'Vehicle Management', 'delete-vehicle' => 'Vehicle Management',
            'create-vehicle-type' => 'Vehicle Management', 'edit-vehicle-type' => 'Vehicle Management', 'delete-vehicle-type' => 'Vehicle Management',

            // Vehicle Maintenance Management
            'vehicle-maintenance-management' => 'Vehicle Maintenance Management',
            'create-maintenance' => 'Vehicle Maintenance Management', 'edit-maintenance' => 'Vehicle Maintenance Management', 'delete-maintenance' => 'Vehicle Maintenance Management',
            'create-operational-log' => 'Vehicle Maintenance Management', 'edit-operational-log' => 'Vehicle Maintenance Management', 'delete-operational-log' => 'Vehicle Maintenance Management',
            'create-vehicle-part' => 'Vehicle Maintenance Management', 'edit-vehicle-part' => 'Vehicle Maintenance Management', 'delete-vehicle-part' => 'Vehicle Maintenance Management',
            'view-maintenance-report' => 'Vehicle Maintenance Management',

            // Generic Document Management
            'document-management' => 'Generic Document Management',
            'create-generic-document' => 'Generic Document Management', 'edit-generic-document' => 'Generic Document Management', 'delete-generic-document' => 'Generic Document Management',
            'create-generic-document-category' => 'Generic Document Management', 'edit-generic-document-category' => 'Generic Document Management', 'delete-generic-document-category' => 'Generic Document Management',
            'create-generic-document-attribute' => 'Generic Document Management', 'edit-generic-document-attribute' => 'Generic Document Management', 'delete-generic-document-attribute' => 'Generic Document Management',

            // Ticket Management
            'ticket-management' => 'Ticket Management',
            'user-ticket-management' => 'Ticket Management',
            'admin-ticket-management' => 'Ticket Management',

            // Invoice Management
            'invoice-management' => 'Invoice Management',
            'create-invoice' => 'Invoice Management', 'edit-invoice' => 'Invoice Management', 'delete-invoice' => 'Invoice Management',
            'create-vendor' => 'Invoice Management', 'edit-vendor' => 'Invoice Management', 'delete-vendor' => 'Invoice Management',
            'create-vat-tax' => 'Invoice Management', 'edit-vat-tax' => 'Invoice Management', 'delete-vat-tax' => 'Invoice Management',
        ];

        foreach ($permissions as $permission) {
            $groupName = $mapping[$permission->name] ?? 'Other Permissions';
            $groups[$groupName][] = $permission;
        }

        return array_filter($groups, function ($group) {
            return count($group) > 0;
        });
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
