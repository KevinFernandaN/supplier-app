<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPhoto extends Model
{
    protected $fillable = ['supplier_id', 'path', 'caption'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
