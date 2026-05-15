<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabDayMenu extends Model
{
    protected $fillable = [
        'rab_day_id', 'menu_id', 'category',
        'is_replacement', 'replaces_id',
        'allergy_pk_count', 'allergy_pb_count',
        'sort_order',
    ];

    protected $casts = [
        'is_replacement' => 'boolean',
    ];

    public static array $categories = [
        'karbo', 'prohe', 'prona', 'sayur',
        'saos', 'garnis', 'buah', 'susu', 'alergen',
    ];

    public function day()
    {
        return $this->belongsTo(RabDay::class, 'rab_day_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    // The original menu this replaces (allergy substitution)
    public function replacesMenu()
    {
        return $this->belongsTo(RabDayMenu::class, 'replaces_id');
    }

    // Replacement menus that point back to this one
    public function replacements()
    {
        return $this->hasMany(RabDayMenu::class, 'replaces_id');
    }

    public function items()
    {
        return $this->hasMany(RabDayMenuItem::class);
    }

    // Effective PK student count for cost calculation
    public function effectivePkCount(): int
    {
        $day = $this->day;
        if ($this->is_replacement) {
            return (int) $this->allergy_pk_count;
        }
        // Subtract all allergy replacements from this menu's base count
        $deducted = $this->replacements->sum('allergy_pk_count');
        return (int) $day->pk_count - (int) $deducted;
    }

    public function effectivePbCount(): int
    {
        $day = $this->day;
        if ($this->is_replacement) {
            return (int) $this->allergy_pb_count;
        }
        $deducted = $this->replacements->sum('allergy_pb_count');
        return (int) $day->pb_count - (int) $deducted;
    }

    // Total RFC for this menu = sum of each ingredient's costFor(pk, pb)
    public function totalCost(): float
    {
        $pk = $this->effectivePkCount();
        $pb = $this->effectivePbCount();

        return $this->items->sum(fn($item) => $item->costFor($pk, $pb));
    }
}
