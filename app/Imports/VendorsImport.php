<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class VendorsImport implements OnEachRow, WithHeadingRow, WithEvents
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
                        $body[] = "{$this->updatedCount} vendor diperbarui datanya.";
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

        $name = 'Unknown Vendor';
        $contactPerson = null;
        $phone = null;
        
        foreach ($row as $key => $value) {
            $keyStr = strtolower(str_replace([' ', '_'], '', (string) $key));
            $valStr = trim((string) $value);
            
            if (str_contains($keyStr, 'name') || str_contains($keyStr, 'namasupplier') || $keyStr === 'nama') {
                if (!empty($valStr)) $name = $valStr;
            }
            if (str_contains($keyStr, 'contact') || str_contains($keyStr, 'kontak') || str_contains($keyStr, 'pic')) {
                if (!empty($valStr)) $contactPerson = $valStr;
            }
            if (str_contains($keyStr, 'phone') || str_contains($keyStr, 'telepon') || str_contains($keyStr, 'telp')) {
                if (!empty($valStr)) $phone = $valStr;
            }
        }

        $data = [
            'name' => $name ?: 'Unknown Vendor',
            'pic_name' => $contactPerson,
            'email' => trim($row['email'] ?? null),
            'phone' => $phone,
            'address' => trim($row['address'] ?? ($row['alamat'] ?? null)),
            'notes' => trim($row['notes'] ?? ($row['catatan'] ?? null)),
        ];

        $existing = Vendor::withTrashed()->where('code', $code)->first();
        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            
            $existing->fill($data);
            
            if ($existing->isDirty()) {
                $existing->save();
                $this->updatedCount++;
            } else {
                $this->skippedCount++;
            }
            return; 
        }

        try {
            Vendor::create(array_merge(['code' => $code], $data));
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062 || $e->errorInfo[0] === '23505') {
                $this->skippedCount++;
                return;
            }
            throw $e;
        }
    }
}
