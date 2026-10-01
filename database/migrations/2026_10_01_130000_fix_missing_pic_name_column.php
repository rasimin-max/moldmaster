<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('maintenances', 'pic_name')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->string('pic_name')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('maintenances', 'pic_name')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->dropColumn('pic_name');
            });
        }
    }
};
