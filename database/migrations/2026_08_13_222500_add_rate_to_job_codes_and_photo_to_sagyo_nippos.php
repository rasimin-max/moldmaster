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
        Schema::table('job_codes', function (Blueprint $table) {
            $table->decimal('rate', 15, 2)->default(0)->after('item');
        });

        Schema::table('sagyo_nippos', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('total_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sagyo_nippos', function (Blueprint $table) {
            $table->dropColumn('photo');
        });

        Schema::table('job_codes', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
    }
};
