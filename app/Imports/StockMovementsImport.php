<?php

namespace App\Imports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class StockMovementsImport implements OnEachRow, WithHeadingRow, WithEvents
{
    public int $skippedCount = 0;
    public int $importedCount = 0;

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                if ($this->skippedCount > 0 || $this->importedCount > 0) {
                    $body = [];
                    if ($this->importedCount > 0) {
                        $body[] = "{$this->importedCount} transaksi berhasil diimport.";
                    }
                    if ($this->skippedCount > 0) {
                        $body[] = "{$this->skippedCount} baris dilewati (kosong atau error).";
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
        
        $itemId = $row['item_id'] ?? ($row['komponen_id'] ?? ($row['alat_id'] ?? null));
        $itemType = trim($row['item_type'] ?? ($row['tipe_item'] ?? ''));
        
        if (empty($itemId) || empty($itemType)) {
            $this->skippedCount++;
            return;
        }

        try {
            StockMovement::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'type' => trim($row['type'] ?? ($row['jenis_transaksi'] ?? 'in')),
                'quantity' => (int)($row['quantity'] ?? ($row['jumlah'] ?? 1)),
                'date' => !empty($row['date']) ? \Carbon\Carbon::parse($row['date']) : now(),
                'reference_number' => trim($row['reference_number'] ?? ($row['referensi'] ?? null)),
                'user_id' => $row['user_id'] ?? null,
                'notes' => trim($row['notes'] ?? ($row['catatan'] ?? null)),
                'status' => trim($row['status'] ?? 'pending'),
            ]);
            $this->importedCount++;
        } catch (\Exception $e) {
            $this->skippedCount++;
        }
    }
}
