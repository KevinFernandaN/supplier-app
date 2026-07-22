<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuRecipe;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MenuRecipeSeeder extends Seeder
{
    public function run(): void
    {
        $gram = Unit::where('symbol', 'g')->value('id');
        $pcs = Unit::where('symbol', 'pcs')->value('id');
        $liter = Unit::where('symbol', 'l')->value('id');

        // menu name => [product name, unit_id, qty]
        $recipes = [
            'Nasi Ayam Goreng' => [
                ['Beras', $gram, 80],
                ['Ayam Fillet', $gram, 100],
                ['Minyak Goreng', $liter, 0.02],
                ['Bawang Putih', $gram, 5],
                ['Garam', $gram, 2],
            ],
            'Nasi Tahu Tempe' => [
                ['Beras', $gram, 80],
                ['Tahu', $pcs, 2],
                ['Tempe', $pcs, 2],
                ['Minyak Goreng', $liter, 0.015],
                ['Bawang Merah', $gram, 5],
            ],
            'Nasi Telur Dadar' => [
                ['Beras', $gram, 80],
                ['Telur', $pcs, 2],
                ['Minyak Goreng', $liter, 0.01],
                ['Garam', $gram, 1],
            ],
        ];

        foreach ($recipes as $menuName => $items) {
            $menuId = Menu::where('name', $menuName)->value('id');

            if (! $menuId) {
                continue;
            }

            foreach ($items as [$productName, $unitId, $qty]) {
                $productId = Product::where('name', $productName)->value('id');

                if (! $productId || ! $unitId) {
                    continue;
                }

                MenuRecipe::firstOrCreate(
                    ['menu_id' => $menuId, 'product_id' => $productId],
                    ['unit_id' => $unitId, 'qty' => $qty]
                );
            }
        }
    }
}
