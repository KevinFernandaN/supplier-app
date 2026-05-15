<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rab_day_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_day_menu_id')->constrained('rab_day_menus')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // Gramasi per portion (replaces single qty)
            $table->decimal('pk_gramasi', 15, 3)->default(0);
            $table->decimal('pb_gramasi', 15, 3)->default(0);

            // Frozen at time of RAB creation from selected supplier's price
            $table->decimal('purchase_price', 15, 4)->default(0);

            $table->timestamps();

            $table->index('product_id');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_day_menu_items');
    }
};
