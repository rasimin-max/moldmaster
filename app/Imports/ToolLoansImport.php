<?php

namespace App\Imports;

use App\Models\ToolLoan;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ToolLoansImport implements OnEachRow, WithHeadingRow, WithEvents
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
                        $body[] = "{$this->importedCount} transaksi peminjaman berhasil diimport.";
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
        
        $toolId = $row['tool_id'] ?? ($row['alat_id'] ?? null);
        $userId = $row['user_id'] ?? ($row['peminjam_id'] ?? null);
        
        if (empty($toolId) || empty($userId)) {
            $this->skippedCount++;
            return;
        }

        $statusStr = strtolower(trim($row['status'] ?? 'borrowed'));
        $status = match(true) {
            str_contains($statusStr, 'pending') || str_contains($statusStr, 'tunda') => 'pending',
            str_contains($statusStr, 'approve') || str_contains($statusStr, 'setuju') => 'approved',
            str_contains($statusStr, 'return') || str_contains($statusStr, 'kembali') => 'returned',
            str_contains($statusStr, 'reject') || str_contains($statusStr, 'tolak') => 'rejected',
            str_contains($statusStr, 'overdue') || str_contains($statusStr, 'telat') => 'overdue',
            default => 'borrowed',
        };

        try {
            ToolLoan::create([
                'tool_id' => $toolId,
                'user_id' => $userId,
                'quantity' => (int)($row['quantity'] ?? ($row['jumlah'] ?? 1)),
                'status' => $status,
                'loan_date' => !empty($row['loan_date']) ? Carbon::parse($row['loan_date']) : now(),
                'return_date' => !empty($row['return_date']) ? Carbon::parse($row['return_date']) : null,
                'notes' => trim($row['notes'] ?? ($row['catatan'] ?? null)),
            ]);
            $this->importedCount++;
        } catch (\Exception $e) {
            $this->skippedCount++;
        }
    }
}
