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
        Schema::table('machines', function (Blueprint $table) {
            try {
                $table->string('type', 50)->change();
            } catch (\Exception $e) {
                // Ignore if it fails due to Postgres enum to varchar casting issues
                // the subsequent migration will manually drop the check constraint.
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // It's hard to revert back to enum without a complex raw query
            // so we'll leave it as string on rollback.
        });
    }
};
