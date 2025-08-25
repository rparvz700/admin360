<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-department|edit-department|delete-department', ['only' => ['index','show']]);
        $this->middleware('permission:create-department', ['only' => ['create','store']]);
        $this->middleware('permission:edit-department', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-department', ['only' => ['destroy']]);
    }


    public function index()
    {
        $activeMenu = "departments";
        $departments = Department::orderBy('id', 'DESC')->get();
        return view('Admin.Departments.index', compact('activeMenu', 'departments'));
    }


    public function create()
    {
        $activeMenu = "departments";
        $users = User::where('status', 1)->get();
        return view('Admin.Departments.create', compact('activeMenu', 'users'));
    }


    public function store(StoreDepartmentRequest $request)
    {
        $data = [
            "name" => $request->name,
            "status" => $request->status,
            "hod" => $request->hod,
            "created_by" => Auth::user()->id
        ];

        $save = Department::create($data);

        if($save){
            return redirect()->route('departments.index')
                ->withSuccess('New department is added successfully.');
        }else{
            return redirect()->route('departments.index')
                ->withSuccess('Something went wrong to add new department.');
        }
    }


    public function edit(Department $department)
    {
        $activeMenu = "departments";
        $users = User::where('status', 1)->get();
        return view('Admin.Departments.edit', compact('users', 'department', 'activeMenu'));
    }


    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $data = [
            "name" => $request->name,
            "status" => $request->status,
            "hod" => $request->hod,
            "last_modified_by" => Auth::user()->id
        ];

        $update = $department->update($data);

        if($update){
            return redirect()->back()
                    ->withSuccess('Department is updated successfully.');
        }else{
            return redirect()->back()
                    ->withError('Something went wrong to update department');
        }
    }


    public function destroy(Department $department)
    {
        $delete = $department->delete();

        if($delete){
        return redirect()->route('departments.index')
                ->withSuccess('Department is deleted successfully.');
        }else{
            return redirect()->route('departments.index')
                ->withSuccess('Something went wrong to delete department');
        }
    }
}
