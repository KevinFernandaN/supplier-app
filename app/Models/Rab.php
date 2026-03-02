<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rab extends Model
{
    protected $fillable = ['region_id', 'menu_id', 'rab_date', 'selling_price', 'notes'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function items()
    {
        return $this->hasMany(RabItem::class);
    }
}
