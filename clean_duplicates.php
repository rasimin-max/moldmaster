<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$barcodes = App\Models\MachineProgram::select('barcode')->groupBy('barcode')->havingRaw('COUNT(barcode) > 1')->pluck('barcode');
foreach ($barcodes as $bc) {
    $duplicates = App\Models\MachineProgram::where('barcode', $bc)->orderBy('id', 'asc')->get();
    $duplicates->shift(); // Keep the first one
    foreach ($duplicates as $dup) {
        $dup->delete();
    }
}
echo "Cleaned duplicates.\n";
