<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabDay extends Model
{
    protected $fillable = [
        'rab_period_id', 'day_date', 'pk_count', 'pb_count', 'realisasi',
    ];

    protected $casts = [
        'day_date' => 'date',
    ];

    public function period()
    {
        return $this->belongsTo(RabPeriod::class, 'rab_period_id');
    }

    public function menus()
    {
        return $this->hasMany(RabDayMenu::class)->orderBy('sort_order')->orderBy('id');
    }

    // Max PK count across all menu items this day (for budget formula)
    public function maxPk(): int
    {
        return (int) $this->pk_count;
    }

    public function maxPb(): int
    {
        return (int) $this->pb_count;
    }

    // Daily budget: MAX(PK) × pk_price + MAX(PB) × pb_price
    public function budget(): float
    {
        $period = $this->period;
        return ($this->maxPk() * $period->pk_price) + ($this->maxPb() * $period->pb_price);
    }

    // Raw food cost: sum of all ingredient costs across all menus this day
    public function rfc(): float
    {
        return $this->menus->sum(fn($menu) => $menu->totalCost());
    }

    // SISA = RFC - Realisasi (variance between planned cost and actual field spend)
    public function surplus(): float
    {
        return $this->rfc() - (float)$this->realisasi;
    }
}
