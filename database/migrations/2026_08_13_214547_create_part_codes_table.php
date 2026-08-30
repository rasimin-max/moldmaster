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
        Schema::create('part_codes', function (Blueprint $table) {
            $table->id();
            $table->string('item'); // ANGULAR BLOCK, CAVITY PLATE, etc.
            $table->string('code')->unique(); // 01-P, 05-P, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_codes');
    }
};
