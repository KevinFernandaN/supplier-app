<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();

            $table->date('sale_date');
            $table->string('channel')->nullable(); // offline/gofood/grab/etc
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['region_id', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
