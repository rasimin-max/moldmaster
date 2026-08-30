<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$project = App\Models\Project::find(1);
$fin = [
    'budget' => $project->budget,
    'actual_cost' => 23516000
];
echo view('filament.pages.project-management-page', ['fin' => $fin, 'costByCategory' => collect([])])->render();
