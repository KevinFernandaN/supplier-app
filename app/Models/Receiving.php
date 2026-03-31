<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receiving extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'kitchen_id',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function items()
    {
        return $this->hasMany(ReceivingItem::class);
    }
}