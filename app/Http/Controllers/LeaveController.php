<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class LeaveController extends Controller
{
    private function hasAnyPermission(Role $role, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                continue;
            }

            if ($role->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        $canView = $this->hasAnyPermission($role, ['leave', 'leave-view', 'leave-add', 'leave-manage']);
        if ($canView) {
            $leaves = Leave::with(['employee', 'leaveType'])->latest()->get();
            $leaveTypes = LeaveType::all();
            $employees = Employee::query()->where('is_active',1)->get();
            $canAdd = $this->hasAnyPermission($role, ['leave', 'leave-add', 'leave-manage']);
            $canManage = $this->hasAnyPermission($role, ['leave', 'leave-manage']);
            return view('backend.hrm.leave.index', compact('leaves', 'leaveTypes', 'employees', 'canAdd', 'canManage'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$this->hasAnyPermission($role, ['leave', 'leave-add', 'leave-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_types' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $days = (strtotime($request->end_date) - strtotime($request->start_date)) / 86400 + 1;

        Leave::create([
            'employee_id' => $request->employee_id,
            'leave_types' => $request->leave_types,
            'start_date' =>  date("Y-m-d", strtotime(str_replace("/", "-", $request->input('start_date')))),
            'end_date' => date("Y-m-d", strtotime(str_replace("/", "-", $request->input('end_date')))),
            'days' => $days,
            'status' => 'Pending',
            'approver_id' => Auth::id()
        ]);

        return redirect()->back()->with('message', __('db.Leave request added successfully'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$this->hasAnyPermission($role, ['leave', 'leave-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $leave = Leave::findOrFail($id);
        // Check if status dropdown submitted
        if ($request->has('status')) {
            $leave->update([
                'status' => $request->status,
                'approver_id' => Auth::id()
            ]);
        } else {
            // Update via edit modal
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'leave_types' => 'required|exists:leave_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            $days = (strtotime($request->end_date) - strtotime($request->start_date)) / 86400 + 1;

            $leave->update([
                'employee_id' => $request->employee_id,
                'leave_types' => $request->leave_types,
                'start_date' =>  date("Y-m-d", strtotime(str_replace("/", "-", $request->input('start_date')))),
                'end_date' => date("Y-m-d", strtotime(str_replace("/", "-", $request->input('end_date')))),
                'days' => $days,
            ]);

        }

        return redirect()->back()->with('message', __('db.Leave updated successfully'));
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$this->hasAnyPermission($role, ['leave', 'leave-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $leave = Leave::findOrFail($id);
        $leave->delete();
        return redirect()->back()->with('message', __('db.Leave deleted successfully'));
    }

    // Bulk delete via AJAX
    public function deleteBySelection(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$this->hasAnyPermission($role, ['leave', 'leave-manage'])) {
            return response()->json(['message' => __('db.Sorry! You are not allowed to access this module')], 403);
        }

        $ids = $request->leaveIdArray;
        Leave::whereIn('id', $ids)->delete();
        return response()->json(['message' => __('db.Selected leaves deleted successfully')]);
    }
}
