<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'region_id',
        'sale_date',
        'channel',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
