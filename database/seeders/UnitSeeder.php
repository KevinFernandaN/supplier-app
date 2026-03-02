<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('units')->insert([
            [
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gram',
                'symbol' => 'g',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Piece',
                'symbol' => 'pcs',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Liter',
                'symbol' => 'l',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
