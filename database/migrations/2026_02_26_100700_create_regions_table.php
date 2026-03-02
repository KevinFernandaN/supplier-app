<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();

            // Short unique code like: JKT, BDG, SBY
            $table->string('code', 20)->unique();

            // Full region name like: Jakarta
            $table->string('name');

            // Useful if in future you expand outside Indonesia
            $table->string('timezone')->default('Asia/Jakarta');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
