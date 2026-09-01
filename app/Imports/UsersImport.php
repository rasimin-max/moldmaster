<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class UsersImport implements OnEachRow, WithHeadingRow, WithEvents
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
                        $body[] = "{$this->updatedCount} user diperbarui datanya.";
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
        
        $email = trim($row['email'] ?? '');
        
        if (empty($email)) {
            $this->skippedCount++;
            return;
        }

        $data = [
            'name' => trim($row['name'] ?? ($row['nama'] ?? 'Unknown User')),
            'role' => trim($row['role'] ?? 'user'),
        ];

        $existing = User::where('email', $email)->first();
        if ($existing) {
            $existing->fill($data);
            
            if (!empty($row['password'])) {
                $existing->password = Hash::make($row['password']);
            }

            if ($existing->isDirty()) {
                $existing->save();
                $this->updatedCount++;
            } else {
                $this->skippedCount++;
            }
            return; 
        }

        try {
            $data['email'] = $email;
            $data['password'] = Hash::make(trim($row['password'] ?? 'password123'));
            User::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                $this->skippedCount++;
                return;
            }
            throw $e;
        }
    }
}
