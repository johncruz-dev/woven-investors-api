<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('grade_level')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['school_id', 'external_id']);
            $table->index(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
