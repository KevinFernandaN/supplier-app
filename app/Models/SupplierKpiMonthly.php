<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierKpiMonthly extends Model
{
    protected $table = 'supplier_kpi_monthly'; // <-- IMPORTANT

    protected $fillable = [
        'region_id',
        'supplier_id',
        'month',
        'review_count',
        'avg_goods_correct',
        'avg_weight_correct',
        'avg_on_time',
        'avg_price_correct',
        'kpi_score',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
