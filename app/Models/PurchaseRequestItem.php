<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'unit_id',
        'required_qty',
        'buffer_pct',
    ];

    protected $casts = [
        'required_qty' => 'decimal:3',
        'buffer_pct'   => 'decimal:2',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Final qty to order = required_qty + buffer
    public function getFinalQtyAttribute(): float
    {
        return round($this->required_qty * (1 + $this->buffer_pct / 100), 3);
    }
}
