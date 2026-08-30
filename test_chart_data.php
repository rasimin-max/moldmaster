<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$project = App\Models\Project::find(1);
$costByCategory = App\Models\Component::whereHas('mold', function($q) use ($project) {
    $q->where('project_id', $project->id);
})->select('category_id', \DB::raw('SUM(stock * unit_price) as cost'))
->groupBy('category_id')->get()->map(function($item) {
    $categoryName = $item->category_id ? App\Models\ComponentCategory::find($item->category_id)->name ?? 'Uncategorized' : 'Uncategorized';
    return ['category' => $categoryName, 'cost' => (float)$item->cost];
})->sortByDesc('cost')->values();

echo "\nLABELS:\n" . json_encode($costByCategory->pluck('category'));
echo "\nDATA:\n" . json_encode($costByCategory->pluck('cost'));
echo "\n";
