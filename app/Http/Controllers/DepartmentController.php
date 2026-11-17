<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        $role = $this->currentRole();
        if($this->hasAnyRolePermission($role, ['department', 'department-view', 'department-add', 'department-manage'])) {
            $lims_department_all = Department::where('is_active', true)->get();
            return view('backend.department.index', compact('lims_department_all'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function store(Request $request)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['department', 'department-add', 'department-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'name' => [
                'max:255',
                    Rule::unique('departments')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);

        $data = $request->all();
        $data['is_active'] = true;
        $department = Department::create($data);

        if($request->ajax()){
            return response()->json($department);
        };
        return redirect('departments')->with('message', __('db.Department created successfully'));
    }

    public function update(Request $request, $id)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['department', 'department-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request,[
            'name' => [
                'max:255',
                Rule::unique('departments')->ignore($request->department_id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);

        $data = $request->all();
        $lims_department_data = Department::find($data['department_id']);
        $lims_department_data->update($data);
        return redirect('departments')->with('message', __('db.Department updated successfully'));
    }

    public function deleteBySelection(Request $request)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['department', 'department-manage'])) {
            return response()->json(['message' => __('db.Sorry! You are not allowed to access this module')], 403);
        }

        $department_id = $request['departmentIdArray'];
        foreach ($department_id as $id) {
            $lims_department_data = Department::find($id);
            $lims_department_data->is_active = false;
            $lims_department_data->save();
        }
        return 'Department deleted successfully!';
    }

    public function destroy($id)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['department', 'department-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_department_data = Department::find($id);
        $lims_department_data->is_active = false;
        $lims_department_data->save();
        return redirect('departments')->with('message', __('db.Department deleted successfully'));
    }
}
