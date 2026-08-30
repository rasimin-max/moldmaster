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
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mold_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('component_id')->nullable()->constrained()->nullOnDelete();
            $table->string('program_name')->nullable();
            $table->integer('planned_duration_minutes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_operation_records', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['mold_id']);
            $table->dropForeign(['component_id']);
            $table->dropColumn([
                'project_id',
                'mold_id',
                'component_id',
                'program_name',
                'planned_duration_minutes',
            ]);
        });
    }
};
