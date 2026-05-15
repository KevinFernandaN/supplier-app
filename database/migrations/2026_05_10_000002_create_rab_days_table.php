<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rab_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_period_id')->constrained('rab_periods')->cascadeOnDelete();

            $table->date('day_date');
            $table->unsignedInteger('pk_count')->default(0);
            $table->unsignedInteger('pb_count')->default(0);

            // Surplus carried in automatically from the previous day
            $table->decimal('carried_over', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['rab_period_id', 'day_date']);
            $table->index('day_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_days');
    }
};
