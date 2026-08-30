<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\ProjectPhase;
use Carbon\Carbon;

// Hapus data lama agar tidak tumpang tindih jika script dijalankan ulang
ProjectPhase::truncate();
Project::where('code', 'like', 'D90-%')->forceDelete();

$now = Carbon::now();

// Proyek 1: Side Sill Garnish
$p1 = Project::create([
    'code' => 'D90-0395',
    'name' => 'Side Sill Garnish',
    'customer' => 'Toyota / Daihatsu',
    'start_date' => $now->copy()->subDays(15),
    'end_date' => $now->copy()->addMonths(2)->addDays(10),
    'budget' => 50000000,
    'status' => 'active',
]);

// Fase Proyek 1
ProjectPhase::create(['project_id' => $p1->id, 'name' => 'Design', 'start_date' => $now->copy()->subDays(15), 'end_date' => $now->copy()->addDays(5), 'estimated_hours' => 120, 'progress' => 100, 'status' => 'completed']);
ProjectPhase::create(['project_id' => $p1->id, 'name' => 'Machining', 'start_date' => $now->copy()->addDays(6), 'end_date' => $now->copy()->addDays(25), 'estimated_hours' => 300, 'progress' => 40, 'status' => 'active']);
ProjectPhase::create(['project_id' => $p1->id, 'name' => 'Assembly', 'start_date' => $now->copy()->addDays(26), 'end_date' => $now->copy()->addMonths(1)->addDays(15), 'estimated_hours' => 250, 'progress' => 0, 'status' => 'pending']);
ProjectPhase::create(['project_id' => $p1->id, 'name' => 'Trial', 'start_date' => $now->copy()->addMonths(1)->addDays(16), 'end_date' => $now->copy()->addMonths(2), 'estimated_hours' => 80, 'progress' => 0, 'status' => 'pending']);


// Proyek 2: RR Bumper
$p2 = Project::create([
    'code' => 'D90-0389',
    'name' => 'RR Bumper Fascia',
    'customer' => 'Toyota / Daihatsu',
    'start_date' => $now->copy()->subDays(5),
    'end_date' => $now->copy()->addMonths(2)->addDays(20),
    'budget' => 75000000,
    'status' => 'active',
]);

// Fase Proyek 2
ProjectPhase::create(['project_id' => $p2->id, 'name' => 'Design', 'start_date' => $now->copy()->subDays(5), 'end_date' => $now->copy()->addDays(15), 'estimated_hours' => 160, 'progress' => 60, 'status' => 'active']);
ProjectPhase::create(['project_id' => $p2->id, 'name' => 'Machining', 'start_date' => $now->copy()->addDays(16), 'end_date' => $now->copy()->addMonths(1)->addDays(5), 'estimated_hours' => 400, 'progress' => 0, 'status' => 'pending']);
ProjectPhase::create(['project_id' => $p2->id, 'name' => 'Assembly', 'start_date' => $now->copy()->addMonths(1)->addDays(6), 'end_date' => $now->copy()->addMonths(2), 'estimated_hours' => 300, 'progress' => 0, 'status' => 'pending']);


// Proyek 3: Roof Panel
$p3 = Project::create([
    'code' => 'D90-0397',
    'name' => 'Roof Panel Inner',
    'customer' => 'Toyota / Daihatsu',
    'start_date' => $now->copy()->addDays(10),
    'end_date' => $now->copy()->addMonths(3),
    'budget' => 60000000,
    'status' => 'active',
]);

// Fase Proyek 3
ProjectPhase::create(['project_id' => $p3->id, 'name' => 'Design', 'start_date' => $now->copy()->addDays(10), 'end_date' => $now->copy()->addDays(25), 'estimated_hours' => 140, 'progress' => 0, 'status' => 'pending']);
ProjectPhase::create(['project_id' => $p3->id, 'name' => 'Machining', 'start_date' => $now->copy()->addDays(26), 'end_date' => $now->copy()->addMonths(1)->addDays(20), 'estimated_hours' => 350, 'progress' => 0, 'status' => 'pending']);
ProjectPhase::create(['project_id' => $p3->id, 'name' => 'Assembly', 'start_date' => $now->copy()->addMonths(1)->addDays(21), 'end_date' => $now->copy()->addMonths(2)->addDays(15), 'estimated_hours' => 280, 'progress' => 0, 'status' => 'pending']);

echo "Dummy data for Projects and Phases created successfully!";
