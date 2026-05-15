<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabDayMenuItem extends Model
{
    protected $fillable = [
        'rab_day_menu_id', 'product_id', 'unit_id', 'supplier_id',
        'pk_gramasi', 'pb_gramasi', 'purchase_price',
    ];

    public function dayMenu()
    {
        return $this->belongsTo(RabDayMenu::class, 'rab_day_menu_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // RFC cost for this ingredient given effective PK/PB counts.
    // Kebutuhan (pk_gramasi / pb_gramasi) is stored in grams per portion;
    // converts to the purchase unit via unit_conversions (e.g. gram → kg).
    public function costFor(int $pk, int $pb): float
    {
        static $gramUnitId = false;
        static $conversions = null;

        if ($gramUnitId === false) {
            $gramUnitId = \App\Models\Unit::whereRaw('LOWER(name) IN ("gram","g","gr","grams")')
                ->value('id');
            $conversions = \Illuminate\Support\Facades\DB::table('unit_conversions')
                ->get()
                ->keyBy(fn($uc) => $uc->from_unit_id . '_' . $uc->to_unit_id);
        }

        $factor = 1.0;
        if ($gramUnitId !== null && (int) $this->unit_id !== (int) $gramUnitId) {
            $key = $gramUnitId . '_' . $this->unit_id;
            if (isset($conversions[$key])) {
                $factor = (float) $conversions[$key]->multiplier;
            } else {
                $invKey = $this->unit_id . '_' . $gramUnitId;
                if (isset($conversions[$invKey])) {
                    $factor = 1.0 / (float) $conversions[$invKey]->multiplier;
                }
            }
        }

        return ((float) $this->pk_gramasi * $factor * $pk
              + (float) $this->pb_gramasi * $factor * $pb)
            * (float) $this->purchase_price;
    }
}
