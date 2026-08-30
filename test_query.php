<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$page = app(App\Filament\Resources\StockMovementResource\Pages\ListStockMovements::class);
$method = new ReflectionMethod($page, "getTableQuery");
$method->setAccessible(true);
$query = $method->invoke($page);
echo "Success!\n";
