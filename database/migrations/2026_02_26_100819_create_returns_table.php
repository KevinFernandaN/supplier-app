<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained('purchase_orders')
                ->nullOnDelete();

            $table->date('return_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['region_id', 'return_date']);
            $table->index(['supplier_id', 'return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
