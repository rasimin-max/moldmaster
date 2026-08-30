<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\ProjectPhase;
use Carbon\Carbon;

// Hapus data lama agar bersih
ProjectPhase::truncate();
Project::whereIn('code', [
    'D90-0395', 'D90-0389', 'D90-0397', 
    'D90-0412', 'D90-0408', 'D90-0403', 
    'A82-0218', 'D90-0420', 'C33-0101'
])->forceDelete();

// Helper untuk membuat fase
function addPhase($projectId, $name, $startDate, $endDate, $estimatedHours, $progress = 0, $status = 'pending') {
    ProjectPhase::create([
        'project_id' => $projectId,
        'name' => $name,
        'start_date' => Carbon::parse($startDate),
        'end_date' => Carbon::parse($endDate),
        'estimated_hours' => $estimatedHours,
        'progress' => $progress,
        'status' => $status
    ]);
}

// 1. D90-0395 - Side Sill Garnish
$p1 = Project::create([
    'code' => 'D90-0395',
    'name' => 'Side Sill Garnish',
    'customer' => 'Toyota / Daihatsu',
    'start_date' => Carbon::parse('2026-03-15'),
    'end_date' => Carbon::parse('2026-06-15'),
    'budget' => 50000000,
    'status' => 'active',
]);
addPhase($p1->id, 'Design', '2026-03-15', '2026-04-10', 200, 100, 'completed');
addPhase($p1->id, 'Machining', '2026-04-10', '2026-05-15', 500, 80, 'active');
addPhase($p1->id, 'Assembly', '2026-05-15', '2026-06-05', 400, 0, 'pending');
addPhase($p1->id, 'Trial', '2026-06-05', '2026-06-15', 100, 0, 'pending');

// 2. D90-0389 - RR Bumper Fascia
$p2 = Project::create([
    'code' => 'D90-0389',
    'name' => 'RR Bumper Fascia',
    'customer' => 'Toyota / Daihatsu',
    'start_date' => Carbon::parse('2026-03-20'),
    'end_date' => Carbon::parse('2026-06-25'),
    'budget' => 75000000,
    'status' => 'active',
]);
addPhase($p2->id, 'Design', '2026-03-20', '2026-04-15', 250, 100, 'completed');
addPhase($p2->id, 'Machining', '2026-04-15', '2026-05-25', 600, 60, 'active');
addPhase($p2->id, 'Assembly', '2026-05-25', '2026-06-15', 450, 0, 'pending');
addPhase($p2->id, 'Trial', '2026-06-15', '2026-06-25', 150, 0, 'pending');

// 3. D90-0397 - Roof Panel Inner (sesuai tooltip di screenshot)
$p3 = Project::create([
    'code' => 'D90-0397',
    'name' => 'Roof Panel Inner',
    'customer' => 'Toyota / Daihatsu',
    'start_date' => Carbon::parse('2026-04-01'),
    'end_date' => Carbon::parse('2026-06-19'),
    'budget' => 60000000,
    'status' => 'active',
]);
addPhase($p3->id, 'Design', '2026-04-01', '2026-04-22', 180, 100, 'completed');
addPhase($p3->id, 'Machining', '2026-04-22', '2026-05-20', 400, 90, 'active');
addPhase($p3->id, 'Assembly', '2026-05-20', '2026-06-10', 300, 0, 'pending');
addPhase($p3->id, 'QC', '2026-06-10', '2026-06-19', 80, 0, 'pending');

// --- Proyek Lain di Legend FUKA Simulation ---

// 4. D90-0412 - FR Bumper
$p4 = Project::create([
    'code' => 'D90-0412', 'name' => 'FR Bumper', 'status' => 'active',
]);
addPhase($p4->id, 'Design', '2026-05-01', '2026-05-25', 200, 50, 'active');
addPhase($p4->id, 'Machining', '2026-05-25', '2026-07-10', 800, 0, 'pending');
addPhase($p4->id, 'Assembly', '2026-07-10', '2026-08-01', 500, 0, 'pending');

// 5. D90-0408 - Engine Cover
$p5 = Project::create([
    'code' => 'D90-0408', 'name' => 'Engine Cover', 'status' => 'active',
]);
addPhase($p5->id, 'Design', '2026-05-10', '2026-06-05', 150, 30, 'active');
addPhase($p5->id, 'Machining', '2026-06-05', '2026-07-20', 600, 0, 'pending');
addPhase($p5->id, 'Assembly', '2026-07-20', '2026-08-15', 350, 0, 'pending');

// 6. D90-0403 - Tailgate
$p6 = Project::create([
    'code' => 'D90-0403', 'name' => 'Tailgate', 'status' => 'active',
]);
addPhase($p6->id, 'Design', '2026-05-20', '2026-06-15', 220, 10, 'active');
addPhase($p6->id, 'Machining', '2026-06-15', '2026-08-05', 900, 0, 'pending');
addPhase($p6->id, 'Assembly', '2026-08-05', '2026-09-01', 600, 0, 'pending');

// 7. A82-0218 - Fog Bezel
$p7 = Project::create([
    'code' => 'A82-0218', 'name' => 'Fog Bezel', 'status' => 'active',
]);
addPhase($p7->id, 'Design', '2026-06-01', '2026-06-20', 120, 0, 'pending');
addPhase($p7->id, 'Machining', '2026-06-20', '2026-07-25', 400, 0, 'pending');
addPhase($p7->id, 'Assembly', '2026-07-25', '2026-08-15', 250, 0, 'pending');

// 8. D90-0420 - B-Pillar
$p8 = Project::create([
    'code' => 'D90-0420', 'name' => 'B-Pillar', 'status' => 'active',
]);
addPhase($p8->id, 'Design', '2026-06-10', '2026-07-01', 180, 0, 'pending');
addPhase($p8->id, 'Machining', '2026-07-01', '2026-08-20', 700, 0, 'pending');
addPhase($p8->id, 'Assembly', '2026-08-20', '2026-09-15', 400, 0, 'pending');

// 9. C33-0101 - Instr. Panel
$p9 = Project::create([
    'code' => 'C33-0101', 'name' => 'Instr. Panel', 'status' => 'active',
]);
addPhase($p9->id, 'Design', '2026-07-01', '2026-07-30', 300, 0, 'pending');
addPhase($p9->id, 'Machining', '2026-07-30', '2026-09-30', 1200, 0, 'pending');
addPhase($p9->id, 'Assembly', '2026-09-30', '2026-10-31', 800, 0, 'pending');

echo "Data real projects (D90-0397, dll) berhasil dimasukkan!";
