<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        "invoice_no", "quotation_id", "delivery_order_id", "purchase_id", "user_id", "biller_id", "customer_id",
        "warehouse_id", "item", "total_qty", "total_discount", "total_tax", "total_price",
        "order_tax_rate", "order_tax", "order_discount", "shipping_cost", "grand_total",
        "paid_amount", "status", "payment_status", "payment_method", "payment_details", "document", "note", "terms", "terms_conditions", "delivery_address", "po_number", "quotation_number", "due_date", "is_active"
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
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
        return $this->hasMany(InvoiceProduct::class);
    }
}
