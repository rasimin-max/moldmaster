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
            $table->dropColumn('program_name');
            $table->foreignId('machine_program_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_operation_records', function (Blueprint $table) {
            $table->dropForeign(['machine_program_id']);
            $table->dropColumn('machine_program_id');
            $table->string('program_name')->nullable();
        });
    }
};
