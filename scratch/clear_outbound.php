<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$countComponent = \App\Models\StockMovement::where('type', 'out')->delete();
echo "Berhasil menghapus $countComponent data Barang Keluar Komponen.\n";

$countTool = \App\Models\ToolMovement::where('type', 'out')->delete();
echo "Berhasil menghapus $countTool data Barang Keluar Alat.\n";
