<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\SupplierProductPrice;
use Illuminate\Database\Seeder;

class SupplierProductSeeder extends Seeder
{
    public function run(): void
    {
        $sembako = Supplier::where('name', 'CV Sumber Rejeki')->first();
        $ayam = Supplier::where('name', 'PT Ayam Segar Nusantara')->first();
        $sayur = Supplier::where('name', 'UD Sayur Makmur')->first();

        if (! $sembako || ! $ayam || ! $sayur) {
            return;
        }

        // [supplier, product name, price, spec, lead_time_days, min_order_qty]
        $map = [
            [$sembako, 'Beras', 13500, 'Beras medium, kemasan 25kg', 1, 25],
            [$sembako, 'Tepung Terigu', 11000, 'Tepung protein sedang, kemasan 25kg', 1, 10],
            [$sembako, 'Gula Pasir', 15000, 'Gula pasir putih, kemasan 50kg', 1, 10],
            [$sembako, 'Garam', 4000, 'Garam beryodium, kemasan 1kg', 2, 5],
            [$sembako, 'Minyak Goreng', 16000, 'Minyak goreng kemasan jerigen 5L', 1, 5],
            [$ayam, 'Ayam Fillet', 38000, 'Fillet dada ayam segar, chiller', 1, 10],
            [$ayam, 'Telur', 2000, 'Telur ayam ras grade A', 1, 100],
            [$sayur, 'Wortel', 9000, 'Wortel lokal segar', 1, 10],
            [$sayur, 'Kentang', 12000, 'Kentang lokal ukuran sedang', 1, 10],
            [$sayur, 'Bawang Merah', 28000, 'Bawang merah lokal', 1, 5],
            [$sayur, 'Bawang Putih', 32000, 'Bawang putih import', 1, 5],
            [$sayur, 'Tahu', 500, 'Tahu putih ukuran sedang', 1, 50],
            [$sayur, 'Tempe', 1500, 'Tempe kedelai kemasan daun/plastik', 1, 50],
        ];

        foreach ($map as [$supplier, $productName, $price, $spec, $leadTime, $minOrder]) {
            $productId = Product::where('name', $productName)->value('id');

            if (! $productId) {
                continue;
            }

            $supplierProduct = SupplierProduct::firstOrCreate(
                ['supplier_id' => $supplier->id, 'product_id' => $productId],
                [
                    'specification_text' => $spec,
                    'lead_time_days' => $leadTime,
                    'min_order_qty' => $minOrder,
                    'is_active' => true,
                    'availability_status' => 'ready',
                ]
            );

            SupplierProductPrice::firstOrCreate(
                [
                    'supplier_product_id' => $supplierProduct->id,
                    'effective_from' => now()->subDays(30)->toDateString(),
                ],
                ['price' => $price]
            );
        }
    }
}
