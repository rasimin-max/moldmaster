<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE machines DROP CONSTRAINT IF EXISTS machines_type_check');
            } else {
                DB::statement('ALTER TABLE machines DROP CONSTRAINT machines_type_check');
            }
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist or syntax error (e.g. on MySQL during build)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
