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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained('kitchens')->restrictOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete();
            $table->decimal('total_portion', 10, 2);
            $table->enum('status', ['draft', 'confirmed', 'ordered'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['kitchen_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
