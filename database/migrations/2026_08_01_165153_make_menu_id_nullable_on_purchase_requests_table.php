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
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->dropColumn('menu_id');
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreignId('menu_id')->nullable()->after('kitchen_id')
                ->constrained('menus')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->dropColumn('menu_id');
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreignId('menu_id')->after('kitchen_id')
                ->constrained('menus')->restrictOnDelete();
        });
    }
};
