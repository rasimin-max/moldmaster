<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$permissions = Spatie\Permission\Models\Permission::pluck('name')->toArray();
echo implode("\n", $permissions);
