<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->text('specification_text')->nullable();
            $table->tinyInteger('lead_time_days')->nullable(); // Hari-1 / Hari-2 -> store 1 / 2
            $table->decimal('min_order_qty', 15, 3)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['supplier_id', 'product_id']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
