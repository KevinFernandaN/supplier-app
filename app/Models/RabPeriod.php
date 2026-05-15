<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabPeriod extends Model
{
    protected $fillable = [
        'region_id', 'name', 'start_date', 'end_date',
        'pk_price', 'pb_price', 'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function days()
    {
        return $this->hasMany(RabDay::class)->orderBy('day_date');
    }

    // Budget for the period = sum of each day's budget
    public function totalBudget(): float
    {
        return $this->days->sum(fn($day) => $day->budget());
    }

    // Total RFC (raw food cost) across all days
    public function totalRfc(): float
    {
        return $this->days->sum(fn($day) => $day->rfc());
    }
}
