<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Seeder;

class UnitConversionSeeder extends Seeder
{
    public function run(): void
    {
        $kg = Unit::where('symbol', 'kg')->value('id');
        $g = Unit::where('symbol', 'g')->value('id');

        if (! $kg || ! $g) {
            return;
        }

        UnitConversion::firstOrCreate(
            ['from_unit_id' => $g, 'to_unit_id' => $kg],
            ['multiplier' => 0.001]
        );

        UnitConversion::firstOrCreate(
            ['from_unit_id' => $kg, 'to_unit_id' => $g],
            ['multiplier' => 1000]
        );
    }
}
