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
        Schema::table('machine_programs', function (Blueprint $table) {
            $table->string('tool_dia')->nullable()->change();
            $table->string('tool_r')->nullable()->change();
            $table->string('tool_length_total')->nullable()->change();
            $table->string('tool_length_eff')->nullable()->change();
            $table->string('ps_thick')->nullable()->change();
            $table->string('rpm')->nullable()->change();
            $table->string('feed')->nullable()->change();
            $table->string('doc')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_programs', function (Blueprint $table) {
            $table->decimal('tool_dia', 8, 2)->nullable()->change();
            $table->decimal('tool_r', 8, 2)->nullable()->change();
            $table->decimal('tool_length_total', 8, 2)->nullable()->change();
            $table->decimal('tool_length_eff', 8, 2)->nullable()->change();
            $table->decimal('ps_thick', 8, 2)->nullable()->change();
            $table->integer('rpm')->nullable()->change();
            $table->integer('feed')->nullable()->change();
            $table->decimal('doc', 8, 2)->nullable()->change();
        });
    }
};
