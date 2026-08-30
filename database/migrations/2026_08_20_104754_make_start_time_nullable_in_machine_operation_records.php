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
            $table->dateTime('start_time')->nullable()->change();
            // Optional: increase status length if necessary, but it's a string so default is 255
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_operation_records', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable(false)->change();
        });
    }
};
