<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biller;
use App\Models\MailSetting;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Auth;
use App\Mail\BillerCreate;
use Mail;

class BillerController extends Controller
{
    use \App\Traits\CacheForget;
    use \App\Traits\TenantInfo;
    use \App\Traits\MailInfo;

    public function index()
    {
        // Biller list access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return redirect('/dashboard')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function create()
    {
        // Biller create access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return redirect('/dashboard')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function store(Request $request)
    {
        // Biller store access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return redirect('/dashboard')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function quickAdd(Request $request)
    {
        // Biller quickAdd access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return response()->json([
            'success' => false,
            'message' => 'Sorry! You are not allowed to access this module'
        ], 403);
    }

    public function edit($id)
    {
        // Biller edit access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return redirect('/dashboard')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function update(Request $request, $id)
    {
        // Biller update access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return redirect('/dashboard')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function importBiller(Request $request)
    {
        // Biller import access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return redirect('/dashboard')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    protected function mailAction($data, $mailSetting)
    {
        $message = 'Data inserted successfully';
        if(!$mailSetting) {
            $message = 'Data inserted successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
        }
        else if($data['email'] && $mailSetting) {
            try{
                $this->setMailInfo($mailSetting);
                Mail::to($data['email'])->send(new BillerCreate($data));
            }
            catch(\Exception $e){
                $message = $e->getMessage();
            }
        }
        return $message;
    }

    public function deleteBySelection(Request $request)
    {
        // Biller delete access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return response()->json([
            'success' => false,
            'message' => 'Sorry! You are not allowed to access this module'
        ], 403);
    }

    public function destroy($id)
    {
        // Biller destroy access blocked - only GLOBAL PRACTISE ENTERPRISE is used
        return redirect('/dashboard')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }
}
