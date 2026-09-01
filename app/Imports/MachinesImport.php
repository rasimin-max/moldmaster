<?php

namespace App\Imports;

use App\Models\Machine;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class MachinesImport implements OnEachRow, WithHeadingRow, WithEvents
{
    public int $skippedCount = 0;
    public int $updatedCount = 0;

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                if ($this->skippedCount > 0 || $this->updatedCount > 0) {
                    $body = [];
                    if ($this->updatedCount > 0) {
                        $body[] = "{$this->updatedCount} item diperbarui datanya.";
                    }
                    if ($this->skippedCount > 0) {
                        $body[] = "{$this->skippedCount} baris dilewati (sudah ada atau kosong).";
                    }

                    Notification::make()
                        ->success()
                        ->title('Import Selesai')
                        ->body(implode(' ', $body))
                        ->send();
                }
            },
        ];
    }

    public function onRow(Row $rowObj)
    {
        $row = $rowObj->toArray();
        
        $code = trim($row['code'] ?? ($row['kode'] ?? ''));
        
        if (empty($code)) {
            $this->skippedCount++;
            return;
        }

        $statusStr = strtolower(trim($row['status'] ?? 'operational'));
        $status = match(true) {
            str_contains($statusStr, 'maintenance') || str_contains($statusStr, 'perbaikan') => 'maintenance',
            str_contains($statusStr, 'breakdown') || str_contains($statusStr, 'rusak') => 'breakdown',
            str_contains($statusStr, 'idle') || str_contains($statusStr, 'diam') => 'idle',
            str_contains($statusStr, 'retired') || str_contains($statusStr, 'pensiun') => 'retired',
            default => 'operational',
        };

        $data = [
            'name' => trim($row['name'] ?? ($row['nama_mesin'] ?? ($row['nama'] ?? 'Unknown Machine'))),
            'type' => trim($row['type'] ?? ($row['tipe'] ?? null)),
            'brand' => trim($row['brand'] ?? ($row['merk'] ?? ($row['merek'] ?? null))),
            'model_number' => trim($row['model_number'] ?? ($row['model'] ?? null)),
            'serial_number' => trim($row['serial_number'] ?? ($row['serial'] ?? null)),
            'area' => trim($row['area'] ?? null),
            'year_purchased' => trim($row['year_purchased'] ?? ($row['tahun'] ?? null)),
            'status' => $status,
            'hourly_rate' => (float)($row['hourly_rate'] ?? ($row['rate'] ?? 0)),
            'notes' => trim($row['notes'] ?? ($row['catatan'] ?? null)),
        ];

        // Jika year_purchased kosong, nullkan
        if (empty($data['year_purchased'])) {
            $data['year_purchased'] = null;
        }

        $existingMachine = Machine::withTrashed()->where('code', $code)->first();
        if ($existingMachine) {
            if ($existingMachine->trashed()) {
                $existingMachine->restore();
            }
            
            $existingMachine->fill($data);
            
            if ($existingMachine->isDirty()) {
                $existingMachine->save();
                $this->updatedCount++;
            } else {
                $this->skippedCount++;
            }
            return; 
        }

        try {
            Machine::create(array_merge(['code' => $code], $data));
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062 || $e->errorInfo[0] === '23505') {
                $this->skippedCount++;
                return;
            }
            throw $e;
        }
    }
}
