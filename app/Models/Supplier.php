<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'region_id',
        'name',
        'phone_wa',
        'address',
        'latitude',
        'longitude',
        'is_active',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function photos()
    {
        return $this->hasMany(SupplierPhoto::class);
    }

    public function certifications()
    {
        return $this->hasMany(SupplierCertification::class);
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function kpis()
    {
        return $this->hasMany(SupplierKpiMonthly::class);
    }
}
