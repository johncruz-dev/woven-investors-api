<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('investments');
        Schema::dropIfExists('investors');
    }

    public function down(): void
    {
        // Investors feature removed; tables are not recreated.
    }
};
