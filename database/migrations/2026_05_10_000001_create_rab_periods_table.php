<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rab_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();

            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');

            $table->unsignedInteger('pk_price')->default(8000);
            $table->unsignedInteger('pb_price')->default(10000);

            $table->enum('status', ['draft', 'confirmed', 'locked'])->default('draft');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['region_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_periods');
    }
};
