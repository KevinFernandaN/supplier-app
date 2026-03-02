<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_kpi_monthly', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();

            $table->char('month', 7); // YYYY-MM
            $table->unsignedInteger('review_count')->default(0);

            $table->decimal('avg_goods_correct', 5, 2)->default(0);
            $table->decimal('avg_weight_correct', 5, 2)->default(0);
            $table->decimal('avg_on_time', 5, 2)->default(0);
            $table->decimal('avg_price_correct', 5, 2)->default(0);

            $table->decimal('kpi_score', 6, 2)->default(0); // weighted final score

            $table->timestamps();

            $table->unique(['region_id', 'supplier_id', 'month']);
            $table->index(['region_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_kpi_monthly');
    }
};
