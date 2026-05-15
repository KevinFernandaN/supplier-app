<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rab_days', function (Blueprint $table) {
            $table->dropColumn('carried_over');
            $table->decimal('realisasi', 12, 2)->default(0)->after('pb_count');
        });
    }

    public function down(): void
    {
        Schema::table('rab_days', function (Blueprint $table) {
            $table->dropColumn('realisasi');
            $table->decimal('carried_over', 12, 2)->default(0)->after('pb_count');
        });
    }
};
