<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuRecipe extends Model
{
    //
    protected $fillable = ['menu_id','product_id','unit_id','qty'];

    public function menu() { return $this->belongsTo(Menu::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
}
