<?php

namespace App\Imports;

use App\Models\Tool;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class ToolsImport implements OnEachRow, WithHeadingRow, WithEvents
{
    public int $skippedCount = 0;
    public int $updatedCount = 0;
    public int $createdCount = 0;
    public int $failedCount = 0;

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                if ($this->skippedCount > 0 || $this->updatedCount > 0 || $this->createdCount > 0 || $this->failedCount > 0) {
                    $body = [];
                    if ($this->createdCount > 0) {
                        $body[] = "{$this->createdCount} item baru berhasil ditambahkan.";
                    }
                    if ($this->updatedCount > 0) {
                        $body[] = "{$this->updatedCount} item diperbarui datanya.";
                    }
                    if ($this->skippedCount > 0) {
                        $body[] = "{$this->skippedCount} item dilewati (sudah ada).";
                    }
                    if ($this->failedCount > 0) {
                        $body[] = "{$this->failedCount} item gagal di-import karena error data.";
                    }

                    if (empty($body)) {
                        $body[] = "Tidak ada baris yang diproses.";
                    }

                    Notification::make()
                        ->success()
                        ->title('Import Selesai')
                        ->body(implode(' ', $body))
                        ->send();
                } else {
                    Notification::make()
                        ->warning()
                        ->title('Import Gagal/Kosong')
                        ->body('Sistem tidak memproses baris apapun. Pastikan format Excel Anda benar dan baris pertama adalah judul kolom (Header).')
                        ->send();
                }
            },
        ];
    }

    public function onRow(Row $rowObj)
    {
        $row = $rowObj->toArray();
        
        $code = trim($row['kode_alat'] ?? ($row['kode'] ?? ($row['kode_qr'] ?? 'TL-' . strtoupper(Str::random(4)))));
        if (empty($code)) {
            $this->failedCount++;
            return;
        }

        $statusStr = strtolower(trim($row['kondisi'] ?? 'baik'));
        $condition = match(true) {
            str_contains($statusStr, 'cukup') => 'fair',
            str_contains($statusStr, 'kurang') => 'poor',
            str_contains($statusStr, 'rusak') => 'damaged',
            default => 'good',
        };

        $data = [
            'name' => trim((string)($row['nama_alat'] ?? 'Unknown Tool')),
            'category' => trim((string)($row['kategori'] ?? '')),
            'total_quantity' => (int)($row['total_qty'] ?? ($row['total'] ?? 1)),
            'available_quantity' => (int)($row['qty_tersedia'] ?? ($row['tersedia'] ?? 1)),
            'condition' => $condition,
            'location' => trim((string)($row['lokasi'] ?? ($row['lokasi_penyimpanan'] ?? ''))),
            'description' => trim((string)($row['deskripsi'] ?? null)),
        ];

        $existingTool = Tool::withTrashed()->where('code', (string)$code)->first();
        if ($existingTool) {
            if ($existingTool->trashed()) {
                $existingTool->restore();
            }
            
            $existingTool->fill($data);
            
            if ($existingTool->isDirty()) {
                $existingTool->save();
                $this->updatedCount++;
            } else {
                $this->skippedCount++;
            }
            return; 
        }

        try {
            Tool::create(array_merge(['code' => $code], $data));
            $this->createdCount++;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ToolsImport Error on Code ' . $code . ': ' . $e->getMessage());
            $this->failedCount++;
        }
    }
}
