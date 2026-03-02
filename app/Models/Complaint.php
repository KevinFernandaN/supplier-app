<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'region_id',
        'purchase_order_item_id',
        'supplier_id',
        'product_id',
        'complaint_date',
        'complaint_type',
        'type',
        'severity',
        'qty',
        'status',
        'description',
        'resolved_at',
    ];

    protected $casts = [
        'complaint_date' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
