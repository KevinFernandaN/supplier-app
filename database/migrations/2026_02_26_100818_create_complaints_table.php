<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->constrained('purchase_order_items')
                ->nullOnDelete();

            $table->date('complaint_date');
            $table->string('type', 50);         // rotten, late, wrong_spec, etc
            $table->tinyInteger('severity');    // 1-5
            $table->text('description')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['region_id', 'complaint_date']);
            $table->index(['product_id', 'complaint_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
