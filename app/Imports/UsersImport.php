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

        $name = 'Unknown User';
        $employeeId = null;
        $role = 'user';
        $password = null;

        foreach ($row as $key => $value) {
            $keyStr = strtolower(str_replace([' ', '_'], '', (string) $key));
            $valStr = trim((string) $value);
            
        if (str_contains($keyStr, 'name') || str_contains($keyStr, 'nama')) {
            if (!empty($valStr)) $name = $valStr;
        }
        if ($keyStr === 'id' || str_contains($keyStr, 'employeeid') || str_contains($keyStr, 'nik') || str_contains($keyStr, 'idkaryawan')) {
            if (!empty($valStr)) $employeeId = $valStr;
        }
        if (str_contains($keyStr, 'role') || str_contains($keyStr, 'peran') || str_contains($keyStr, 'akses')) {
            if (!empty($valStr)) $role = $valStr;
        }
        if (str_contains($keyStr, 'password') || str_contains($keyStr, 'sandi') || str_contains($keyStr, 'pass')) {
            if (!empty($valStr)) $password = $valStr;
        }
        }

        $data = [
            'name' => $name,
            'employee_id' => $employeeId,
            'role' => $role,
        ];

        $existing = User::where('email', $email)->first();
        if ($existing) {
            $existing->fill($data);
            
            if (!empty($password)) {
                $existing->password = Hash::make($password);
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
            $data['password'] = Hash::make($password ?: 'password123');
            User::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062 || $e->errorInfo[0] === '23505') {
                $this->skippedCount++;
                return;
            }
            throw $e;
        }
    }
}
