<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        // Panggil command shield untuk memastikan semua permission baru dari HasPageShield tergenerate
        \Illuminate\Support\Facades\Artisan::call('shield:generate', ['--all' => true]);

        // Permission yang SEHARUSNYA untuk operator (sesuai gambar Menu Operator)
        $permissions = [
            'page_SagyoNippoEntryPage',          // Sagyo Nippo
            'page_TakeItemPage',                 // Ambil Barang
            'page_ReturnItemPage',               // Pengembalian Barang
            'page_BorrowToolPage',               // Pinjam Alat
            'page_ReturnToolPage',               // Pengembalian Alat
            'page_RequestPartToolPage',          // Request Part / Tool
            'page_ReportAbnormalityPage',        // Lapor Abnormality
            'page_ReportImprovementPage',        // Info Improvement
            'view_any_machine::operation::record', // Machine Operation Records (berada di Resource)
        ];

        // Pastikan permissions exist di tabel
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        
        // syncPermissions akan me-replace permission lama (termasuk membuang ResumeSagyoNippo dll)
        $role->syncPermissions($permissions);
    }

    public function down(): void
    {
        //
    }
};
