<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();

            $table->date('review_date');

            // rating inputs (1-5)
            $table->tinyInteger('goods_correct');
            $table->tinyInteger('weight_correct');
            $table->tinyInteger('on_time');
            $table->tinyInteger('price_correct');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'review_date']);
            $table->index(['region_id', 'review_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_reviews');
    }
};
