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
        Schema::table('molds', function (Blueprint $table) {
            $table->string('mold_number', 50)->nullable()->after('id')->comment('Nomor Mold (misal: 425)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('molds', function (Blueprint $table) {
            $table->dropColumn('mold_number');
        });
    }
};
