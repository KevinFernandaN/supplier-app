<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['name' => 'Nasi Ayam Goreng', 'default_selling_price' => 15000],
            ['name' => 'Nasi Tahu Tempe', 'default_selling_price' => 12000],
            ['name' => 'Nasi Telur Dadar', 'default_selling_price' => 11000],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(['name' => $menu['name']], $menu);
        }
    }
}
