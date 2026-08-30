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
            $table->decimal('estimated_time', 8, 2)->nullable()->after('description')->comment('Estimated time in hours/minutes as inputted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_programs', function (Blueprint $table) {
            $table->dropColumn('estimated_time');
        });
    }
};
