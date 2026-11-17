<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\HrmSetting;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Auth;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function index()
    {
        $role = $this->currentRole();
        if ($this->hasAnyRolePermission($role, ['shift', 'shift-view', 'shift-add', 'shift-manage'])) {
            $lims_hrm_setting_data = HrmSetting::latest()->first();
            $lims_shift_all = Shift::where('is_active', true)->get();

            $lims_hrm_setting_data = HrmSetting::latest()->first();
            return view('backend.hrm.shift.index', compact('lims_shift_all','lims_hrm_setting_data'));

        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['shift', 'shift-add', 'shift-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'name' => [
                'required',
                'max:255',
                Rule::unique('shifts')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);

        $data = $request->all();
        $data['start_time'] = Carbon::parse($request->start_time)->format('H:i:s');
        $data['end_time']   = Carbon::parse($request->end_time)->format('H:i:s');
        $data['is_active'] = true;
        $shift = Shift::create($data);
        if($request->ajax()){
             return response()->json($shift);
        }

        return redirect('shift')->with('message', __('db.Shift created successfully'));
    }

    public function update(Request $request, $id)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['shift', 'shift-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'name' => [
                'required',
                'max:255',
                Rule::unique('shifts')->ignore($request->shift_id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);

        $data = $request->all();
        $data['start_time'] = Carbon::parse($request->start_time)->format('H:i:s');
        $data['end_time']   = Carbon::parse($request->end_time)->format('H:i:s');
        $data['is_active'] = true;
        $lims_shift_data = Shift::find($data['shift_id']);
        $lims_shift_data->update($data);

        return redirect('shift')->with('message', __('db.Shift updated successfully'));
    }

    public function deleteBySelection(Request $request)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['shift', 'shift-manage'])) {
            return response()->json(['message' => __('db.Sorry! You are not allowed to access this module')], 403);
        }

        $shift_id = $request['shiftIdArray'];
        foreach ($shift_id as $id) {
            $lims_shift_data = Shift::find($id);
            $lims_shift_data->is_active = false;
            $lims_shift_data->save();
        }
        return 'Shift deleted successfully!';
    }

    public function destroy($id)
    {
        $role = $this->currentRole();
        if (!$this->hasAnyRolePermission($role, ['shift', 'shift-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_shift_data = Shift::find($id);
        $lims_shift_data->is_active = false;
        $lims_shift_data->save();
        return redirect('shift')->with('message', __('db.Shift deleted successfully'));
    }
}
