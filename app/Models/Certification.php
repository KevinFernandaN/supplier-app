<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = ['name', 'issuer'];

    public function supplierCertifications()
    {
        return $this->hasMany(SupplierCertification::class);
    }
}
