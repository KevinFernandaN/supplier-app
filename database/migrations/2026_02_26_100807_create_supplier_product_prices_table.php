<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_product_id')->constrained('supplier_products')->cascadeOnDelete();

            $table->decimal('price', 15, 2);
            $table->date('effective_from');
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->index(['supplier_product_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_product_prices');
    }
};
