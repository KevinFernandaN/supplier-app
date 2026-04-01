<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'qty')) {
                $after = Schema::hasColumn('complaints', 'complaint_type') ? 'complaint_type' : 'type';
                $table->decimal('qty', 15, 3)->nullable()->after($after);
            }
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'qty')) {
                $table->dropColumn('qty');
            }
        });
    }
};
