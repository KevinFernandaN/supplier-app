<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    //
    public function products()
    {
        return $this->hasMany(Product::class, 'base_unit_id');
    }
}
