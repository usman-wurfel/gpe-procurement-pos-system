<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $fillable = [
        "delivery_order_no", "quotation_id", "purchase_id", "user_id", "biller_id", "customer_id", "warehouse_id",
        "item", "total_qty", "total_discount", "total_tax", "total_price", "order_tax_rate",
        "order_tax", "order_discount", "shipping_cost", "grand_total", "status", "document",
        "note", "delivery_address", "delivery_date", "po_number", "quotation_number", "is_active"
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function biller()
    {
        return $this->belongsTo(Biller::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function products()
    {
        return $this->hasMany(DeliveryOrderProduct::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Restrict query for staff: matches list + dashboard counts/recent widgets.
     * "own" = created by user OR same warehouse (so admin-created DOs still show for warehouse staff).
     */
    public function scopeVisibleToStaff(Builder $query, $user = null): Builder
    {
        $user = $user ?? auth()->user();
        if (!$user || (int) $user->role_id <= 2) {
            return $query;
        }

        $access = config('staff_access');
        $table = $query->getModel()->getTable();

        if ($access === 'own') {
            return $query->where(function (Builder $q) use ($user, $table) {
                $q->where($table.'.user_id', $user->id);
                if ($user->warehouse_id) {
                    $q->orWhere($table.'.warehouse_id', $user->warehouse_id);
                }
            });
        }

        if ($access === 'warehouse') {
            return $query->where($table.'.warehouse_id', $user->warehouse_id);
        }

        return $query;
    }
}
