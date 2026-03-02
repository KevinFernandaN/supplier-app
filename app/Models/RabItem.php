<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabItem extends Model
{
    protected $fillable = ['rab_id', 'product_id', 'unit_id', 'qty', 'purchase_price'];

    public function rab()
    {
        return $this->belongsTo(Rab::class);
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
