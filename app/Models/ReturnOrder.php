<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnOrder extends Model
{
    //
    protected $table = 'returns'; // IMPORTANT (because model name != table name)

    protected $fillable = [
        'region_id','supplier_id','return_date','status','notes',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\ReturnItem::class);
    }
}
