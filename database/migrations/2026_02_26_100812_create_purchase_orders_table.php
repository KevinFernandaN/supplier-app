<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();

            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();

            $table->string('status', 30)->default('draft'); // draft/confirmed/received/cancelled
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['region_id', 'order_date']);
            $table->index(['supplier_id', 'order_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
