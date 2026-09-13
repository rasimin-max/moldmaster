<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\MachineOperationRecord;
use App\Models\MachineProgram;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $records = MachineOperationRecord::whereNull('barcode')->whereNotNull('machine_program_id')->get();
        foreach ($records as $record) {
            $program = MachineProgram::find($record->machine_program_id);
            if ($program) {
                $record->barcode = $program->barcode;
                // use saveQuietly so it doesn't trigger observers that might alter duration
                $record->saveQuietly();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for this backfill
    }
};
