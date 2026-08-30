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
        Schema::create('job_codes', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // A. CAD / DESIGN, B. CAM / PROGRAM, etc.
            $table->string('item'); // DESIGN CONCEPT, 3D MODELING, etc.
            $table->string('code')->unique(); // A-1, A-2, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_codes');
    }
};
