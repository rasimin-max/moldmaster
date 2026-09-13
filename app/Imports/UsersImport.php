<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class UsersImport implements OnEachRow, WithEvents
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
        // Get row as indexed array
        $row = array_values($rowObj->toArray());
        
        // Check if this is a header row by looking at the second column
        $col1 = strtolower(trim($row[1] ?? ''));
        if ($col1 === 'id' || str_contains($col1, 'employee') || str_contains($col1, 'nik')) {
            return; // Skip header row
        }

        // Map by indices based on user's Excel format
        // 0: Avatar, 1: ID, 2: Name, 3: Email, 4: Role, 5: Area, 6: Aktif
        $employeeId = trim($row[1] ?? '');
        $name = trim($row[2] ?? '');
        $email = trim($row[3] ?? '');
        $role = trim($row[4] ?? '');
        $password = null;

        if (empty($name)) {
            $name = 'Unknown User';
        }
        if (empty($role)) {
            $role = 'user';
        }

        $rolesArray = [];
        foreach (explode(',', $role) as $r) {
            $r = strtolower(trim($r));
            $r = str_replace(' ', '_', $r);
            if (!empty($r)) {
                $rolesArray[] = $r;
            }
        }

        if (empty($email)) {
            if ($employeeId) {
                $email = strtolower($employeeId) . '@moldmaster.id';
            } else {
                $email = 'user_' . uniqid() . '@moldmaster.id';
            }
        }

        $data = [
            'name' => $name,
            'employee_id' => $employeeId,
        ];

        $existing = User::where('email', $email)->first();
        if ($existing) {
            $existing->fill($data);
            
            if (!empty($password)) {
                $existing->password = Hash::make($password);
            }

            if ($existing->isDirty() || count($rolesArray) > 0) {
                try {
                    $existing->save();
                    if (count($rolesArray) > 0) {
                        $existing->syncRoles($rolesArray);
                    }
                    $this->updatedCount++;
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] == 1062 || $e->errorInfo[0] === '23505') {
                        $this->skippedCount++;
                        return;
                    }
                    throw $e;
                }
            } else {
                $this->skippedCount++;
            }
            return; 
        }

        try {
            $data['email'] = $email;
            $data['password'] = Hash::make($password ?: 'password123');
            $newUser = User::create($data);
            if (count($rolesArray) > 0) {
                $newUser->syncRoles($rolesArray);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062 || $e->errorInfo[0] === '23505') {
                $this->skippedCount++;
                return;
            }
            throw $e;
        }
    }
}
