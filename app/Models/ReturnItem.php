<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    //
    protected $fillable = [
        'return_order_id','product_id','qty','unit_id','reason',
    ];

    public function returnOrder()
    {
        return $this->belongsTo(\App\Models\ReturnOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class);
    }
}
