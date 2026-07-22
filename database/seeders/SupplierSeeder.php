<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $regionId = Region::where('is_active', true)->orderBy('id')->value('id')
            ?? Region::orderBy('id')->value('id');

        if (! $regionId) {
            return;
        }

        $suppliers = [
            [
                'name' => 'CV Sumber Rejeki',
                'phone_wa' => '081234567001',
                'address' => 'Jl. Raya Pasar Induk No. 12, Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ],
            [
                'name' => 'PT Ayam Segar Nusantara',
                'phone_wa' => '081234567002',
                'address' => 'Jl. Industri Peternakan No. 5, Jakarta',
                'latitude' => -6.1751,
                'longitude' => 106.8650,
            ],
            [
                'name' => 'UD Sayur Makmur',
                'phone_wa' => '081234567003',
                'address' => 'Jl. Pasar Sayur No. 8, Jakarta',
                'latitude' => -6.2297,
                'longitude' => 106.8261,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['region_id' => $regionId, 'name' => $supplier['name']],
                $supplier + ['region_id' => $regionId, 'is_active' => true]
            );
        }
    }
}
