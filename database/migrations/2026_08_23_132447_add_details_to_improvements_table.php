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
        if (Schema::hasColumn('improvements', 'photo')) {
            Schema::table('improvements', function (Blueprint $table) {
                $table->renameColumn('photo', 'photo_before');
            });
        }
        Schema::table('improvements', function (Blueprint $table) {
            if (!Schema::hasColumn('improvements', 'photo_after')) {
                $table->string('photo_after')->nullable()->after('photo_before');
            }
            if (!Schema::hasColumn('improvements', 'cost_effect')) {
                $table->decimal('cost_effect', 15, 2)->nullable()->after('status');
                $table->date('implementation_date')->nullable()->after('cost_effect');
                $table->decimal('cost_investment', 15, 2)->nullable()->after('implementation_date');
            }
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('improvements', function (Blueprint $table) {
            $table->renameColumn('photo_before', 'photo');
            $table->dropColumn(['photo_after', 'cost_effect', 'implementation_date', 'cost_investment']);
        });
    }
};
