<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_reviews', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->unique(['purchase_order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('supplier_reviews', function (Blueprint $table) {
            $table->dropUnique(['purchase_order_id']);
            $table->dropConstrainedForeignId('purchase_order_id');
        });
    }
};
