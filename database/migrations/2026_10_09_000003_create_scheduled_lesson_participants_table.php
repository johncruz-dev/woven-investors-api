<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_lesson_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['scheduled_lesson_id', 'user_id'], 'scheduled_lesson_participant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_lesson_participants');
    }
};
