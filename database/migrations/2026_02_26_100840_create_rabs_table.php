<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rabs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete();

            $table->date('rab_date'); // your “order date”
            $table->decimal('selling_price', 15, 2); // frozen price for this sheet
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['region_id', 'menu_id', 'rab_date']);
            $table->index(['region_id', 'rab_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rabs');
    }
};
