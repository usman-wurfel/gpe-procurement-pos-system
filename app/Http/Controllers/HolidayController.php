<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;
use Auth;
use User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Mail;
use App\Mail\HolidayApprove;
use App\Models\MailSetting;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class HolidayController extends Controller
{
    use \App\Traits\MailInfo;

    public function index()
    {
        $role = $this->currentRole();
        if($this->hasAnyRolePermission($role, ['holiday', 'holiday-manage'])) {
            $approve_permission = true;
            $lims_holiday_list = Holiday::orderBy('id', 'desc')->get();
        }
        elseif($this->hasAnyRolePermission($role, ['holiday-view', 'holiday-add'])) {
            $approve_permission = false;
            $lims_holiday_list = Holiday::where('user_id', Auth::id())->orderBy('id', 'desc')->get();
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        return view('backend.hrm.holiday.index', compact('lims_holiday_list', 'approve_permission'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['holiday', 'holiday-add', 'holiday-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $data = [
            'from_date'   => date("Y-m-d", strtotime(str_replace("/", "-", $request->input('from_date')))),
            'to_date'     => date("Y-m-d", strtotime(str_replace("/", "-", $request->input('to_date')))),
            'user_id'     => Auth::id(),
            'note'        => $request->input('note'),
            'recurring' => $request->input('recurring') ?? 0,
            'region' => $request->input('region') ?? null
        ];

        if($this->hasAnyRolePermission($role, ['holiday', 'holiday-manage'])) {
            $data['is_approved'] = true;
        }
        else{
            $data['is_approved'] = false;
        }
        Holiday::create($data);
        return redirect()->back()->with('message', __("db.Holiday created successfully"));
    }

    public function show($id)
    {
        //
    }

    public function approveHoliday($id)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['holiday', 'holiday-manage'])) {
            return response()->json(['message' => __('db.Sorry! You are not allowed to access this module')], 403);
        }

        $holiday = Holiday::find($id);
        $holiday->is_approved = true;
        $holiday->save();
        //collecting mail data
        $mail_data['name'] = $holiday->user->name;
        $mail_data['email'] = $holiday->user->email;
        $mail_setting = MailSetting::latest()->first();
        if($mail_setting) {
            $this->setMailInfo($mail_setting);
            try {
                Mail::to($mail_data['email'])->send(new HolidayApprove($mail_data));
                return 'Holiday approved successfully!';
            }
            catch(\Exception $e) {
                return 'Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
    }

    public function myHoliday($year, $month)
    {
        $start = 1;
        $number_of_day = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        while($start <= $number_of_day)
        {
            if($start < 10)
                $date = $year.'-'.$month.'-0'.$start;
            else
                $date = $year.'-'.$month.'-'.$start;
            $holiday_found = Holiday::whereDate('from_date','<=', $date)
                ->whereDate('to_date','>=', $date)
                ->where([
                    ['is_approved', true],
                    ['user_id', Auth::id()]
                ])->first();
            if($holiday_found) {
                $general_setting = \App\Models\GeneralSetting::select('date_format')->latest()->first();
                $holidays[$start] = date($general_setting->date_format, strtotime($holiday_found->from_date)).' '.__("db.To").' '.date($general_setting->date_format, strtotime($holiday_found->to_date));
            }
            else {
                $holidays[$start] = false;
            }
            $start++;
        }
        //return dd($holidays);
        $start_day = date('w', strtotime($year.'-'.$month.'-01')) + 1;
        $prev_year = date('Y', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
        $prev_month = date('m', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
        $next_year = date('Y', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
        $next_month = date('m', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
        return view('backend.hrm.holiday.my_holiday', compact('start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'holidays'));
    }

    public function update(Request $request, $id)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['holiday', 'holiday-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $holiday_data = Holiday::find($request->input('id'));
        $data = [
            'from_date'   => date("Y-m-d", strtotime(str_replace("/", "-", $request->input('from_date')))),
            'to_date'     => date("Y-m-d", strtotime(str_replace("/", "-", $request->input('to_date')))),
            'note'        => $request->input('note'),
            'recurring' => $request->input('recurring') ?? 0,
            'region' => $request->input('region') ?? null
        ];
        $holiday_data->update($data);
        return redirect()->back()->with('message', __("db.Holiday updated successfully"));
    }

    public function deleteBySelection(Request $request)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['holiday', 'holiday-manage'])) {
            return response()->json(['message' => __('db.Sorry! You are not allowed to access this module')], 403);
        }

        $holiday_id = $request['holidayIdArray'];
        foreach ($holiday_id as $id) {
            $lims_holiday_data = Holiday::find($id);
            $lims_holiday_data->delete();
        }
        return 'Holiday deleted successfully!';
    }

    public function destroy($id)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['holiday', 'holiday-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        Holiday::find($id)->delete();
        return redirect()->back()->with('not_prmitted', __("db.Holiday deleted successfully"));
    }
    public function importExcel(Request $request)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['holiday', 'holiday-add', 'holiday-manage'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'date_format' => 'required|in:d/m/Y,m/d/Y,Y/m/d,d-m-Y,m-d-Y,Y-m-d'
        ]);

        $upload = $request->file('file');
        $dateFormat = $request->input('date_format');
        
        try {
            $spreadsheet = IOFactory::load($upload->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip header row
            array_shift($rows);
            
            $imported = 0;
            $errors = [];
            
            $hasPermission = $this->hasAnyRolePermission($role, ['holiday', 'holiday-manage']);
            
            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }
                
                try {
                    $fromDateStr = trim($row[0]);
                    $toDateStr = trim($row[1] ?? $row[0]); // If to_date not provided, use from_date
                    $note = trim($row[2] ?? '');
                    $recurring = isset($row[3]) ? (intval($row[3]) ? 1 : 0) : 0;
                    $region = isset($row[4]) ? trim($row[4]) : null;
                    
                    // Parse dates based on selected format
                    $fromDate = Carbon::createFromFormat($dateFormat, $fromDateStr)->format('Y-m-d');
                    $toDate = Carbon::createFromFormat($dateFormat, $toDateStr)->format('Y-m-d');
                    
                    // Validate date range
                    if ($toDate < $fromDate) {
                        $errors[] = "Row " . ($index + 2) . ": To date cannot be before From date";
                        continue;
                    }
                    
                    $data = [
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                        'user_id' => Auth::id(),
                        'note' => $note,
                        'recurring' => $recurring,
                        'region' => $region,
                        'is_approved' => $hasPermission ? true : false
                    ];
                    
                    Holiday::create($data);
                    $imported++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }
            
            $message = __("db.Holiday imported successfully") . ". " . $imported . " " . __("db.records imported");
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " " . __("db.errors occurred");
            }
            
            return redirect()->back()->with('message', $message)->with('import_errors', $errors);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('not_permitted', __("db.Error importing file") . ": " . $e->getMessage());
        }
    }
}
