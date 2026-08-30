<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\Project::with(['phases' => function ($q) { $q->orderBy('start_date'); }])->find(1);
$m1 = now()->startOfMonth();
$m2 = $m1->copy()->addMonths(6)->endOfMonth();
$totalDays = $m1->diffInDays($m2);
foreach($p->phases as $phase) {
    $startOffset = max(0, $m1->diffInDays($phase->start_date, false));
    $duration = $phase->start_date->diffInDays($phase->end_date);
    $leftPercent = max(0, ($startOffset / $totalDays) * 100);
    $widthPercent = min(100 - $leftPercent, ($duration / $totalDays) * 100);
    
    echo 'Phase: ' . $phase->name . ', Start Offset: ' . $startOffset . ', Duration: ' . $duration . ', Left %: ' . $leftPercent . ', Width %: ' . $widthPercent . "\n";
}
