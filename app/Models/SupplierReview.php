<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierReview extends Model
{
    protected $fillable = [
        'region_id',
        'supplier_id',
        'purchase_order_id',
        'review_date',
        'goods_correct',
        'weight_correct',
        'on_time',
        'price_correct',
        'notes',
    ];

    protected $casts = [
        'review_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
