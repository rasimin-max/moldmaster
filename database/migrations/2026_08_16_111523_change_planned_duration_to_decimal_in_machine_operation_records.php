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
        Schema::table('machine_operation_records', function (Blueprint $table) {
            $table->decimal('planned_duration_minutes', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_operation_records', function (Blueprint $table) {
            $table->integer('planned_duration_minutes')->nullable()->change();
        });
    }
};
