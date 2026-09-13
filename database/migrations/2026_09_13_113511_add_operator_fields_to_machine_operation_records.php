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
            $table->string('barcode')->nullable();
            $table->string('shift')->nullable();
            $table->integer('manual_hours')->nullable();
            $table->integer('manual_minutes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_operation_records', function (Blueprint $table) {
            $table->dropColumn(['barcode', 'shift', 'manual_hours', 'manual_minutes']);
        });
    }
};
