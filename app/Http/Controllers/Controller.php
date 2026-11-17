<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\ActivityLog;
use App\Mail\UserLogMessage;
use App\Mail\AdminLogMessage;
use App\Traits\FileHandleTrait;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, FileHandleTrait;

    protected function currentRole(): ?Role
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return Role::find($user->role_id);
    }

    protected function hasAnyRolePermission(?Role $role, array $permissions): bool
    {
        if (! $role) {
            return false;
        }

        $user = auth()->user();
        if ($user && (int) $user->role_id <= 2 && (int) $user->role_id === (int) $role->id) {
            return true;
        }

        $names = role_permission_names((int) $role->id);

        foreach ($permissions as $permission) {
            if (in_array($permission, $names, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Empty hidden inputs become null (ConvertEmptyStringsToNull). MySQL NOT NULL
     * columns (total_qty, etc.) fail on insert — fill from line arrays or zero.
     */
    protected function normalizeOrderDocumentTotals(array &$data, bool $withPaidAmount = false): void
    {
        $toFloat = function ($value, float $fallback = 0.0): float {
            if ($value === null || $value === '') {
                return $fallback;
            }
            if (!is_numeric($value)) {
                return $fallback;
            }

            return (float) $value;
        };

        $qtyList = $data['qty'] ?? [];
        $sumQty = 0.0;
        foreach ((array) $qtyList as $q) {
            $sumQty += $toFloat($q, 0.0);
        }

        $sumColumn = function (string $key) use (&$data, $toFloat): float {
            $sum = 0.0;
            foreach ((array) ($data[$key] ?? []) as $v) {
                $sum += $toFloat($v, 0.0);
            }

            return $sum;
        };

        $totalQtyIn = $data['total_qty'] ?? null;
        $data['total_qty'] = ($totalQtyIn === null || $totalQtyIn === '' || !is_numeric($totalQtyIn))
            ? $sumQty
            : (float) $totalQtyIn;

        $tpIn = $data['total_price'] ?? null;
        $data['total_price'] = ($tpIn === null || $tpIn === '' || !is_numeric($tpIn))
            ? $sumColumn('subtotal')
            : (float) $tpIn;

        $ttIn = $data['total_tax'] ?? null;
        $data['total_tax'] = ($ttIn === null || $ttIn === '' || !is_numeric($ttIn))
            ? $sumColumn('tax')
            : (float) $ttIn;

        $tdIn = $data['total_discount'] ?? null;
        $data['total_discount'] = ($tdIn === null || $tdIn === '' || !is_numeric($tdIn))
            ? 0.0
            : (float) $tdIn;

        $otIn = $data['order_tax'] ?? null;
        if ($otIn === null || $otIn === '' || !is_numeric($otIn)) {
            $rate = $toFloat($data['order_tax_rate'] ?? 0, 0.0);
            $data['order_tax'] = $data['total_price'] * $rate / 100;
        } else {
            $data['order_tax'] = (float) $otIn;
        }

        $gtIn = $data['grand_total'] ?? null;
        if ($gtIn === null || $gtIn === '' || !is_numeric($gtIn)) {
            $data['grand_total'] = $data['total_price'] + $data['order_tax']
                - $toFloat($data['order_discount'] ?? 0, 0.0)
                + $toFloat($data['shipping_cost'] ?? 0, 0.0);
        } else {
            $data['grand_total'] = (float) $gtIn;
        }

        $itemIn = $data['item'] ?? null;
        if ($itemIn === null || $itemIn === '' || !is_numeric($itemIn)) {
            $data['item'] = count((array) ($data['product_id'] ?? []));
        } else {
            $data['item'] = (int) $itemIn;
        }

        if ($withPaidAmount) {
            $paIn = $data['paid_amount'] ?? null;
            $data['paid_amount'] = ($paIn === null || $paIn === '' || !is_numeric($paIn))
                ? 0.0
                : (float) $paIn;
        }
    }

    public function setSuccessMessage($message)
	{
    	session()->flash('message',$message);
    	session()->flash('type','success');
        return redirect()->back();
	}

	public function setErrorMessage($message)
	{
		session()->flash('not_permitted',$message);
		session()->flash('type','danger');
        return redirect()->back();
	}

	public function createActivityLog($log_data)
	{
		ActivityLog::create($log_data);
		if(isset($log_data['mail_setting']) && isset($log_data['admin_email'])) {
			$this->setMailInfo($log_data['mail_setting']);
			Mail::to($log_data['admin_email'])->send(new AdminLogMessage($log_data));
			if($log_data['user_email'])
				Mail::to($log_data['user_email'])->send(new UserLogMessage($log_data));
		}
	}
}
