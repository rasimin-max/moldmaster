<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$csv = "kode_qr,nama_komponen,spesifikasi_ukuran,bagian,material,machining,nama_project,nomor_mold\n42501,EJECTOR,20X20X40,CORE BASE,STEEL,CNC,Project A,425";
file_put_contents('test.csv', $csv);

try {
    \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ComponentsImport(), 'test.csv');
    echo "Import success\n";
    
    $comp = \App\Models\Component::where('code', '42501')->with(['machiningType', 'mold.project'])->first();
    echo "Machining: " . ($comp->machiningType->name ?? 'none') . "\n";
    echo "Project: " . ($comp->mold->project->name ?? 'none') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
