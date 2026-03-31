<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivingItem extends Model
{
    protected $fillable = [
        'receiving_id',
        'purchase_order_item_id',
        'product_id',
        'unit_id',
        'ordered_qty',
        'received_qty',
        'notes',
        'proof_image',
    ];

    public function receiving()
    {
        return $this->belongsTo(Receiving::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}