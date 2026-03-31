<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('unit_id')->constrained();
            $table->decimal('qty', 15, 3);
            $table->string('type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->text('notes')->nullable();
            $table->timestamp('moved_at');
            $table->timestamps();

            $table->index(['product_id', 'moved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
