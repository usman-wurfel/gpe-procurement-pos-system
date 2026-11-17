<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Overtime;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class OvertimeController extends Controller
{
    public function index()
    {
        $role = $this->currentRole();
            if ($this->hasAnyRolePermission($role, ['overtime', 'overtime-view', 'overtime-add', 'overtime-manage'])) {
                $overtimes = Overtime::with('employee')->get();
                $employees = Employee::all();
                $canAdd = $this->hasAnyRolePermission($role, ['overtime', 'overtime-add', 'overtime-manage']);
                $canManage = $this->hasAnyRolePermission($role, ['overtime', 'overtime-manage']);
                return view('backend.hrm.overtime.index', compact('overtimes', 'employees', 'canAdd', 'canManage'));
            } else {
                return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
            }
    }

    public function store(Request $request)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['overtime', 'overtime-add', 'overtime-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $normalizedDate = null;
        if ($request->filled('date')) {
            try {
                $normalizedDate = Carbon::parse(str_replace('/', '-', $request->input('date')))->format('Y-m-d');
                $request->merge(['date' => $normalizedDate]);
            } catch (\Exception $e) {
                return redirect()->back()->with('not_permitted', __('db.Invalid date format'));
            }
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0'
        ]);
        $data = $request->all();
        $data['date'] = $normalizedDate ?? date("Y-m-d", strtotime(str_replace("/", "-", $request->input('date'))));
        Overtime::create($data);
        return redirect()->back()->with('message', 'Overtime added successfully');
    }

    public function update(Request $request, $id)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['overtime', 'overtime-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $normalizedDate = null;
        if ($request->filled('date')) {
            try {
                $normalizedDate = Carbon::parse(str_replace('/', '-', $request->input('date')))->format('Y-m-d');
                $request->merge(['date' => $normalizedDate]);
            } catch (\Exception $e) {
                return redirect()->back()->with('not_permitted', __('db.Invalid date format'));
            }
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'status' => 'required|in:pending,approved,rejected'
        ]);
        $data = $request->all();
        $data['date'] = $normalizedDate ?? date("Y-m-d", strtotime(str_replace("/", "-", $request->input('date'))));
        $overtime = Overtime::findOrFail($id);
        $overtime->update($data);

        return redirect()->back()->with('message', 'Overtime updated successfully');
    }

    public function destroy($id)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['overtime', 'overtime-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $overtime = Overtime::findOrFail($id);
        $overtime->delete();
        return redirect()->back()->with('message', 'Overtime deleted successfully');
    }
}
