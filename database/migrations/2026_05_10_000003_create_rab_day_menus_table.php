<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rab_day_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_day_id')->constrained('rab_days')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete();

            $table->enum('category', [
                'karbo', 'prohe', 'prona', 'sayur',
                'saos', 'garnis', 'buah', 'susu', 'alergen'
            ]);

            // Allergy replacement fields
            $table->boolean('is_replacement')->default(false);
            $table->foreignId('replaces_id')->nullable()->constrained('rab_day_menus')->nullOnDelete();
            $table->unsignedInteger('allergy_pk_count')->nullable();
            $table->unsignedInteger('allergy_pb_count')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rab_day_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_day_menus');
    }
};
