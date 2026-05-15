<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['code', 'name', 'timezone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function kitchens()
    {
        return $this->hasMany(Kitchen::class);
    }
}
