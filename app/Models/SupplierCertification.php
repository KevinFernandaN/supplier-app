<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierCertification extends Model
{
    protected $fillable = [
        'supplier_id',
        'certification_id',
        'certificate_no',
        'issued_at',
        'expired_at',
        'file_path',
    ];

    protected $casts = [
        'issued_at'  => 'date',
        'expired_at' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function certification()
    {
        return $this->belongsTo(Certification::class);
    }
}
