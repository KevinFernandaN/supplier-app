<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $kg = Unit::where('symbol', 'kg')->value('id');
        $pcs = Unit::where('symbol', 'pcs')->value('id');
        $l = Unit::where('symbol', 'l')->value('id');

        $products = [
            ['name' => 'Beras', 'category' => 'Karbohidrat', 'base_unit_id' => $kg],
            ['name' => 'Tepung Terigu', 'category' => 'Karbohidrat', 'base_unit_id' => $kg],
            ['name' => 'Ayam Fillet', 'category' => 'Protein Hewani', 'base_unit_id' => $kg],
            ['name' => 'Telur', 'category' => 'Protein Hewani', 'base_unit_id' => $pcs],
            ['name' => 'Tahu', 'category' => 'Protein Nabati', 'base_unit_id' => $pcs],
            ['name' => 'Tempe', 'category' => 'Protein Nabati', 'base_unit_id' => $pcs],
            ['name' => 'Wortel', 'category' => 'Sayur', 'base_unit_id' => $kg],
            ['name' => 'Kentang', 'category' => 'Sayur', 'base_unit_id' => $kg],
            ['name' => 'Bawang Merah', 'category' => 'Bumbu', 'base_unit_id' => $kg],
            ['name' => 'Bawang Putih', 'category' => 'Bumbu', 'base_unit_id' => $kg],
            ['name' => 'Gula Pasir', 'category' => 'Bumbu', 'base_unit_id' => $kg],
            ['name' => 'Garam', 'category' => 'Bumbu', 'base_unit_id' => $kg],
            ['name' => 'Minyak Goreng', 'category' => 'Bumbu', 'base_unit_id' => $l],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}
