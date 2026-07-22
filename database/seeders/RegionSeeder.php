<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        Region::firstOrCreate(
            ['code' => 'JKT'],
            [
                'name' => 'Jakarta',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]
        );
    }
}
