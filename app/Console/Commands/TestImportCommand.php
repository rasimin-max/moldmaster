<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tool;
use Illuminate\Support\Str;

class TestImportCommand extends Command
{
    protected $signature = 'app:test-import';
    protected $description = 'Command description';

    public function handle()
    {
        $rows = [
            // Row 2
            ['foto' => 'tools/01.jpg', 'kode' => '2202', 'nama_alat' => 'KUNCI L', 'tipe_alat' => '', 'kategori' => 'HAND TOOL', 'tersedia' => 1, 'total' => 1, 'condition' => 'Baik', 'lokasi' => 'Lemari A-01'],
            // Row 3
            ['foto' => '', 'kode' => '2203', 'nama_alat' => 'KUNCI L', 'tipe_alat' => '', 'kategori' => 'HAND TOOL', 'tersedia' => 2, 'total' => 2, 'condition' => 'Baik', 'lokasi' => 'Lemari A-02'],
            // Row 4
            ['foto' => '', 'kode' => '2204', 'nama_alat' => 'KUNCI L', 'tipe_alat' => '', 'kategori' => 'HAND TOOL', 'tersedia' => 3, 'total' => 3, 'condition' => 'Baik', 'lokasi' => 'Lemari A-03'],
        ];

        $skippedCount = 0;
        $updatedCount = 0;
        $createdCount = 0;
        $failedCount = 0;

        foreach ($rows as $row) {
            $code = trim($row['kode_alat'] ?? ($row['kode'] ?? ($row['kode_qr'] ?? 'TL-' . strtoupper(Str::random(4)))));
            if (empty($code)) {
                $failedCount++;
                continue;
            }

            $statusStr = strtolower(trim($row['kondisi'] ?? 'baik'));
            $condition = match(true) {
                str_contains($statusStr, 'cukup') => 'fair',
                str_contains($statusStr, 'kurang') => 'poor',
                str_contains($statusStr, 'rusak') => 'damaged',
                default => 'good',
            };

            $data = [
                'name' => trim($row['nama_alat'] ?? 'Unknown Tool'),
                'category' => trim($row['kategori'] ?? ''),
                'total_quantity' => (int)($row['total_qty'] ?? ($row['total'] ?? 1)),
                'available_quantity' => (int)($row['qty_tersedia'] ?? ($row['tersedia'] ?? 1)),
                'condition' => $condition,
                'location' => trim($row['lokasi'] ?? ($row['lokasi_penyimpanan'] ?? '')),
                'description' => trim($row['deskripsi'] ?? null),
            ];

            $existingTool = Tool::withTrashed()->where('code', $code)->first();
            if ($existingTool) {
                if ($existingTool->trashed()) {
                    $existingTool->restore();
                }
                
                $existingTool->fill($data);
                
                if ($existingTool->isDirty()) {
                    $existingTool->save();
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
                continue; 
            }

            try {
                Tool::create(array_merge(['code' => $code], $data));
                $createdCount++;
            } catch (\Exception $e) {
                $this->error('ToolsImport Error on Code ' . $code . ': ' . $e->getMessage());
                $failedCount++;
            }
        }
        
        $this->info("Skipped: " . $skippedCount);
        $this->info("Created: " . $createdCount);
        $this->info("Updated: " . $updatedCount);
        $this->info("Failed: " . $failedCount);
    }
}

