<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierProductPrice extends Model
{
    protected $fillable = [
        'supplier_product_id',
        'price',
        'effective_from',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
    ];

    public function supplierProduct()
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
