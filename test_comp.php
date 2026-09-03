<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$comps = \App\Models\Component::all();
foreach($comps as $comp) {
    echo $comp->code . " - " . $comp->name . "\n";
}
