<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->timestamps();

            $table->unique(['name', 'issuer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_photos');
    }
};
