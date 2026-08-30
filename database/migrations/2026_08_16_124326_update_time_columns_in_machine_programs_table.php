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
            $table->string('actual_time')->nullable()->after('estimated_time');
            $table->string('estimated_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_programs', function (Blueprint $table) {
            $table->dropColumn('actual_time');
            $table->decimal('estimated_time', 8, 2)->nullable()->change();
        });
    }
};
