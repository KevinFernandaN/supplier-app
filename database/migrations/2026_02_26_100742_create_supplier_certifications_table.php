<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_certifications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnDelete();

            $table->foreignId('certification_id')
                ->constrained('certifications')
                ->restrictOnDelete();

            $table->string('certificate_no')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->string('file_path')->nullable();

            $table->timestamps();

            $table->index(['supplier_id', 'certification_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_certifications');
    }
};
