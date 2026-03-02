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
        Schema::table('supplier_kpi_monthly', function (Blueprint $table) {
            $table->decimal('complaint_penalty', 5, 2)->default(0)->after('kpi_score');
            $table->decimal('return_penalty', 5, 2)->default(0)->after('complaint_penalty');
            $table->unsignedInteger('return_count')->default(0)->after('return_penalty');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_kpi_monthly', function (Blueprint $table) {
            $table->dropColumn(['complaint_penalty', 'return_penalty', 'return_count']);
        });
    }
};
