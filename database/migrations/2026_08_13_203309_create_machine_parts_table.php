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
        Schema::create('machine_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('part_number')->nullable();
            $table->date('installed_at')->nullable();
            $table->integer('expected_life_hours')->nullable()->comment('Expected lifespan in hours');
            $table->integer('expected_life_cycles')->nullable()->comment('Expected lifespan in cycles/shots');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_parts');
    }
};
