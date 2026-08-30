<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Administrator',
                'employee_id' => 'EMP-000',
                'email' => 'superadmin@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000000',
                'area' => null,
                'is_active' => true,
                'role' => 'super_admin',
            ],
            [
                'name' => 'Budi Santoso',
                'employee_id' => 'EMP-001',
                'email' => 'admin@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000001',
                'area' => null,
                'is_active' => true,
                'role' => 'admin',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'employee_id' => 'EMP-002',
                'email' => 'admin2@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000002',
                'area' => null,
                'is_active' => true,
                'role' => 'admin',
            ],
            [
                'name' => 'Hendra Wijaya',
                'employee_id' => 'EMP-003',
                'email' => 'leader@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000003',
                'area' => 'CNC',
                'is_active' => true,
                'role' => 'leader',
            ],
            [
                'name' => 'Siti Rahayu',
                'employee_id' => 'EMP-004',
                'email' => 'leader2@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000004',
                'area' => 'EDM',
                'is_active' => true,
                'role' => 'leader',
            ],
            [
                'name' => 'Dian Pratama',
                'employee_id' => 'EMP-005',
                'email' => 'leader3@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000005',
                'area' => 'Assembly',
                'is_active' => true,
                'role' => 'leader',
            ],
            [
                'name' => 'Operator Satu',
                'employee_id' => 'EMP-010',
                'email' => 'operator@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000010',
                'area' => 'CNC',
                'is_active' => true,
                'role' => 'operator',
            ],
            [
                'name' => 'Rizky Aditya',
                'employee_id' => 'EMP-011',
                'email' => 'operator2@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000011',
                'area' => 'EDM',
                'is_active' => true,
                'role' => 'operator',
            ],
            [
                'name' => 'Fajar Nugroho',
                'employee_id' => 'EMP-012',
                'email' => 'operator3@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000012',
                'area' => 'Wirecut',
                'is_active' => true,
                'role' => 'operator',
            ],
            [
                'name' => 'Eko Susanto',
                'employee_id' => 'EMP-013',
                'email' => 'operator4@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000013',
                'area' => 'Grinding',
                'is_active' => true,
                'role' => 'operator',
            ],
            [
                'name' => 'Wahyu Setiawan',
                'employee_id' => 'EMP-014',
                'email' => 'operator5@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000014',
                'area' => 'Polishing',
                'is_active' => true,
                'role' => 'operator',
            ],
            [
                'name' => 'Direktur Utama',
                'employee_id' => 'EMP-020',
                'email' => 'viewer@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000020',
                'area' => null,
                'is_active' => true,
                'role' => 'viewer',
            ],
            [
                'name' => 'Manager Produksi',
                'employee_id' => 'EMP-021',
                'email' => 'viewer2@moldmaster.id',
                'password' => Hash::make('password'),
                'phone' => '081200000021',
                'area' => null,
                'is_active' => true,
                'role' => 'viewer',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            $user = User::updateOrCreate(['email' => $userData['email']], $userData);
            $user->syncRoles([$role]);
        }

        $this->command->info('Users seeded successfully!');
    }
}
