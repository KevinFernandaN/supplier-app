<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');   // kilogram, gram, pcs
            $table->string('symbol', 20); // kg, g, pcs
            $table->timestamps();

            $table->unique(['name', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
