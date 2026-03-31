<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    //
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function kitchens()
    {
        return $this->hasMany(Kitchen::class);
    }
}
