<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'default_selling_price',
    ];

    public function recipes()
    {
        return $this->hasMany(MenuRecipe::class);
    }
}
