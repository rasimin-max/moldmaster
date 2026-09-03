<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = [
    'kode_qr' => '42502',
    'nama_komponen' => 'EJECTOR',
    'spesifikasi_ukuran' => '10X10X150',
    'bagian' => 'CORE BASE',
    'material' => 'STEEL',
    'nomor_mold' => '425',
    'kebutuhan' => 6,
    'barang_masuk' => 2,
    'barang_dipakai' => 0,
    'stok_sekarang' => 2,
    'belum_datang' => 4,
];

class MockRow extends \Maatwebsite\Excel\Row {
    private $data;
    public function __construct($data) { $this->data = $data; }
    public function toArray($nullValue = null, $calculateFormulas = false, $formatData = true, ?string $endColumn = null) { return $this->data; }
}

try {
    $import = new App\Imports\ComponentsImport();
    $import->onRow(new MockRow($row));
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
