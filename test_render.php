<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$projects = App\Models\Project::with(['phases' => function ($query) {
    $query->orderBy('start_date');
}])->where('status', 'active')->get();

$startMonth = now()->startOfMonth();
$months = collect(range(0, 6))->map(function($i) use ($startMonth) {
    return $startMonth->copy()->addMonths($i);
});

$totalDays = $months->first()->diffInDays($months->last()->endOfMonth());

echo "Total Days: " . $totalDays . "\n";

foreach($projects as $project) {
    echo "Project: " . $project->code . " has " . $project->phases->count() . " phases\n";
    
    foreach($project->phases as $phase) {
        if(!$phase->start_date || !$phase->end_date) {
            echo "  Skipped phase (no dates)\n";
            continue;
        }
        
        $timelineStart = $months->first();
        $timelineEnd = $months->last()->endOfMonth();
        
        $startOffset = max(0, $timelineStart->diffInDays($phase->start_date, false));
        $duration = $phase->start_date->diffInDays($phase->end_date);
        
        $leftPercent = max(0, ($startOffset / $totalDays) * 100);
        $widthPercent = min(100 - $leftPercent, ($duration / $totalDays) * 100);
        
        echo "  Phase: " . $phase->name . "\n";
        echo "    start_date: " . $phase->start_date->toDateString() . "\n";
        echo "    end_date:   " . $phase->end_date->toDateString() . "\n";
        echo "    startOffset: " . $startOffset . "\n";
        echo "    duration: " . $duration . "\n";
        echo "    leftPercent: " . $leftPercent . "%\n";
        echo "    widthPercent: " . $widthPercent . "%\n";
    }
}
