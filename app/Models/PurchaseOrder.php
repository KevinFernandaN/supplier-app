<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    //
    protected $fillable = [
        'region_id',
        'supplier_id',
        'purchase_request_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function review()
    {
        return $this->hasOne(SupplierReview::class);
    }

    public function receivings()
    {
        return $this->hasMany(Receiving::class);
    }
}
