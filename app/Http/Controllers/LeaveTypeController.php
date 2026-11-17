<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $role = $this->currentRole();
        if ($this->hasAnyRolePermission($role, ['leave-type', 'leave-type-view', 'leave-type-add', 'leave-type-manage'])) {
            $leaveTypes = LeaveType::all();
            return view('backend.hrm.leave_type.index', compact('leaveTypes'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['leave-type', 'leave-type-add', 'leave-type-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'annual_quota' => 'required|numeric|min:0',
            'encashable' => 'required|boolean',
            'carry_forward_limit' => 'required|numeric|min:0',
        ]);

        $leaveType = LeaveType::create($request->all());
        if($request->ajax()){
            return response()->json([
            'id' => $leaveType->id,
            'name' => $leaveType->name
        ]);
        }
        return redirect()->back()->with('message', 'Leave Type added successfully');
    }

    public function update(Request $request, $id)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['leave-type', 'leave-type-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name,' . $id,
            'annual_quota' => 'required|numeric|min:0',
            'encashable' => 'required|boolean',
            'carry_forward_limit' => 'required|numeric|min:0',
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $data = $request->except('leave_type_id');
        $leaveType->update($data);
        return redirect()->back()->with('message', 'Leave Type updated successfully');
    }

    public function destroy($id)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['leave-type', 'leave-type-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $leaveType = LeaveType::findOrFail($id);
        $leaveType->delete();
        return redirect()->back()->with('message', 'Leave Type deleted successfully');
    }
}
