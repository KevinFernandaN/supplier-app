<?php

namespace Database\Seeders;

use App\Models\Kitchen;
use App\Models\Region;
use Illuminate\Database\Seeder;

class KitchenSeeder extends Seeder
{
    public function run(): void
    {
        $regionId = Region::where('is_active', true)->orderBy('id')->value('id')
            ?? Region::orderBy('id')->value('id');

        if (! $regionId) {
            return;
        }

        $kitchens = [
            [
                'name' => 'Dapur Utama Jakarta',
                'type' => 'assisted',
                'address' => 'Jl. Dapur Sentral No. 1, Jakarta',
            ],
            [
                'name' => 'Dapur Cabang Bekasi',
                'type' => 'open',
                'address' => 'Jl. Dapur Cabang No. 2, Bekasi',
            ],
        ];

        foreach ($kitchens as $kitchen) {
            Kitchen::firstOrCreate(
                ['region_id' => $regionId, 'name' => $kitchen['name']],
                $kitchen + ['region_id' => $regionId, 'is_active' => true]
            );
        }
    }
}
