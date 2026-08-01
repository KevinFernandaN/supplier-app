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

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    // H-1 lock date = the day before cooking starts (start_date).
    // Matches the RAB PK/PB workflow: confirmed H-1, locked from start_date onward.
    public function prLockDate()
    {
        return $this->start_date->copy()->subDay()->startOfDay();
    }

    public function isPastPrLockDate(): bool
    {
        return now()->startOfDay()->gte($this->prLockDate());
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
