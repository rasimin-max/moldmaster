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
        Schema::create('sagyo_nippos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mold_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_code_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('hours', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sagyo_nippos');
    }
};
