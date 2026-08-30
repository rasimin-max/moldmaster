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
        Schema::create('machine_operation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Operator
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('operation_type')->default('production'); // production, setup, trial, maintenance
            $table->integer('cycles')->nullable();
            $table->string('status')->default('running'); // running, completed
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_operation_records');
    }
};
