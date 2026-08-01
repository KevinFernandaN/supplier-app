<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'kitchen_id',
        'menu_id',
        'rab_period_id',
        'total_portion',
        'status',
        'notes',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function rabPeriod()
    {
        return $this->belongsTo(RabPeriod::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}
