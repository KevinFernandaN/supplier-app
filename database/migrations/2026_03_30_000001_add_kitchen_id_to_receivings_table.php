<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receivings', function (Blueprint $table) {
            // Must drop FK before dropping the unique index it depends on
            $table->dropForeign(['purchase_order_id']);
            $table->dropUnique('receivings_purchase_order_id_unique');

            // Re-add FK without unique (allows multiple receivings per PO)
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();

            // Which kitchen is doing this receiving
            $table->foreignId('kitchen_id')->nullable()->after('purchase_order_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('receivings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kitchen_id');
            $table->dropForeign(['purchase_order_id']);
            $table->unique('purchase_order_id');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
        });
    }
};
