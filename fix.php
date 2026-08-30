<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$records = App\Models\MachineOperationRecord::whereNotNull('machine_program_id')->get();
foreach($records as $mor) {
    if ($mor->machineProgram && $mor->machineProgram->estimated_time) {
        $mor->planned_duration_minutes = (float)str_replace(',', '.', $mor->machineProgram->estimated_time);
        $mor->save();
        echo "Updated record ID {$mor->id} with plan {$mor->planned_duration_minutes}\n";
    }
}
echo "Done\n";
