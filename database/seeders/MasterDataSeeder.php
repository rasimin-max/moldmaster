<?php

namespace Database\Seeders;

use App\Models\ComponentCategory;
use App\Models\Machine;
use App\Models\Mold;
use App\Models\Tool;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Component Categories
        $categories = [
            ['name' => 'Insert', 'slug' => 'insert', 'color' => '#6366f1'],
            ['name' => 'Ejector', 'slug' => 'ejector', 'color' => '#f59e0b'],
            ['name' => 'Pin', 'slug' => 'pin', 'color' => '#10b981'],
            ['name' => 'Spring', 'slug' => 'spring', 'color' => '#3b82f6'],
            ['name' => 'Bolt', 'slug' => 'bolt', 'color' => '#8b5cf6'],
            ['name' => 'Guide', 'slug' => 'guide', 'color' => '#ec4899'],
            ['name' => 'Slide', 'slug' => 'slide', 'color' => '#14b8a6'],
            ['name' => 'Core', 'slug' => 'core', 'color' => '#f97316'],
        ];
        foreach ($categories as $cat) {
            ComponentCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Vendors
        $vendors = [
            [
                'code' => 'VND-001',
                'name' => 'PT. Mold Komponen Utama',
                'address' => 'Jl. Industri Raya No. 45, Karawang, Jawa Barat',
                'pic_name' => 'Bpk. Hendro Susanto',
                'phone' => '021-89012345',
                'email' => 'sales@moldkomp.co.id',
                'lead_time_days' => 7,
                'status' => 'active',
            ],
            [
                'code' => 'VND-002',
                'name' => 'CV. Teknik Presisi Mandiri',
                'address' => 'Jl. Raya Bekasi KM 25, Cikarang, Bekasi',
                'pic_name' => 'Ibu. Dewi Anggraini',
                'phone' => '021-89023456',
                'email' => 'order@tpresisi.com',
                'lead_time_days' => 14,
                'status' => 'active',
            ],
            [
                'code' => 'VND-003',
                'name' => 'PT. Baja Spesial Indonesia',
                'address' => 'Jl. Gatot Subroto No. 12, Jakarta Selatan',
                'pic_name' => 'Bpk. Agus Pranoto',
                'phone' => '021-52456789',
                'email' => 'purchasing@bajaspec.co.id',
                'lead_time_days' => 21,
                'status' => 'active',
            ],
            [
                'code' => 'VND-004',
                'name' => 'UD. Karya Logam Abadi',
                'address' => 'Jl. Raya Bogor KM 42, Depok',
                'pic_name' => 'Bpk. Wahyudi',
                'phone' => '021-77812345',
                'email' => 'wahyudi@karyalogam.id',
                'lead_time_days' => 5,
                'status' => 'active',
            ],
        ];
        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(['code' => $vendor['code']], $vendor);
        }

        // Molds
        $molds = [
            [
                'code' => 'MOL-2024-001',
                'name' => 'Bumper Front Avanza Gen3',
                'project_name' => 'Toyota Avanza 2024',
                'customer' => 'PT. Toyota Motor Manufacturing',
                'product_type' => 'Bumper',
                'cavity' => 1,
                'shot_life' => 500000,
                'current_shot' => 125000,
                'status' => 'active',
            ],
            [
                'code' => 'MOL-2024-002',
                'name' => 'Grille Front Brio Facelift',
                'project_name' => 'Honda Brio 2024 FL',
                'customer' => 'PT. Honda Prospect Motor',
                'product_type' => 'Grille',
                'cavity' => 2,
                'shot_life' => 300000,
                'current_shot' => 87500,
                'status' => 'active',
            ],
            [
                'code' => 'MOL-2024-003',
                'name' => 'Side Skirt Xpander Cross',
                'project_name' => 'Mitsubishi Xpander Cross',
                'customer' => 'PT. Mitsubishi Motors Krama Yudha',
                'product_type' => 'Side Skirt',
                'cavity' => 1,
                'shot_life' => 400000,
                'current_shot' => 200000,
                'status' => 'active',
            ],
            [
                'code' => 'MOL-2023-005',
                'name' => 'Bumper Rear Innova Zenix',
                'project_name' => 'Toyota Innova Zenix 2023',
                'customer' => 'PT. Toyota Motor Manufacturing',
                'product_type' => 'Bumper',
                'cavity' => 1,
                'shot_life' => 500000,
                'current_shot' => 350000,
                'status' => 'maintenance',
            ],
            [
                'code' => 'MOL-2022-010',
                'name' => 'Dashboard Panel HR-V Gen2',
                'project_name' => 'Honda HR-V Gen 2',
                'customer' => 'PT. Honda Prospect Motor',
                'product_type' => 'Dashboard',
                'cavity' => 1,
                'shot_life' => 200000,
                'current_shot' => 195000,
                'status' => 'inactive',
            ],
        ];
        foreach ($molds as $mold) {
            Mold::firstOrCreate(['code' => $mold['code']], $mold);
        }

        // Machines
        $machines = [
            ['code' => 'CNC-001', 'name' => 'Machining Center A', 'type' => 'CNC', 'brand' => 'Fanuc', 'area' => 'CNC', 'year_purchased' => 2020, 'status' => 'operational', 'hourly_rate' => 150000],
            ['code' => 'CNC-002', 'name' => 'Machining Center B', 'type' => 'CNC', 'brand' => 'Mazak', 'area' => 'CNC', 'year_purchased' => 2021, 'status' => 'operational', 'hourly_rate' => 175000],
            ['code' => 'EDM-001', 'name' => 'EDM Sinker 1', 'type' => 'EDM', 'brand' => 'Makino', 'area' => 'EDM', 'year_purchased' => 2019, 'status' => 'operational', 'hourly_rate' => 120000],
            ['code' => 'EDM-002', 'name' => 'EDM Sinker 2', 'type' => 'EDM', 'brand' => 'Sodick', 'area' => 'EDM', 'year_purchased' => 2022, 'status' => 'maintenance', 'hourly_rate' => 130000],
            ['code' => 'WC-001', 'name' => 'Wire Cut 1', 'type' => 'Wirecut', 'brand' => 'Fanuc', 'area' => 'Wirecut', 'year_purchased' => 2020, 'status' => 'operational', 'hourly_rate' => 100000],
            ['code' => 'GRD-001', 'name' => 'Surface Grinder 1', 'type' => 'Grinding', 'brand' => 'Okamoto', 'area' => 'Grinding', 'year_purchased' => 2018, 'status' => 'operational', 'hourly_rate' => 80000],
            ['code' => 'GRD-002', 'name' => 'Surface Grinder 2', 'type' => 'Grinding', 'brand' => 'Okamoto', 'area' => 'Grinding', 'year_purchased' => 2019, 'status' => 'breakdown', 'hourly_rate' => 80000],
            ['code' => 'MLG-001', 'name' => 'Milling Machine 1', 'type' => 'Milling', 'brand' => 'Bridgeport', 'area' => 'CNC', 'year_purchased' => 2017, 'status' => 'operational', 'hourly_rate' => 70000],
            ['code' => 'POL-001', 'name' => 'Polishing Station 1', 'type' => 'Polishing', 'brand' => 'Local', 'area' => 'Polishing', 'year_purchased' => 2021, 'status' => 'operational', 'hourly_rate' => 50000],
            ['code' => 'LZR-001', 'name' => 'Laser Engraver 1', 'type' => 'Laser', 'brand' => 'Trumpf', 'area' => 'Assembly', 'year_purchased' => 2023, 'status' => 'operational', 'hourly_rate' => 200000],
        ];
        foreach ($machines as $machine) {
            Machine::firstOrCreate(['code' => $machine['code']], $machine);
        }

        // Tools
        $tools = [
            ['code' => 'TL-001', 'name' => 'Kunci Torsi', 'category' => 'Hand Tools', 'total_quantity' => 5, 'available_quantity' => 5, 'condition' => 'good', 'location' => 'Lemari A-01'],
            ['code' => 'TL-002', 'name' => 'Dial Gauge', 'category' => 'Measuring', 'total_quantity' => 8, 'available_quantity' => 8, 'condition' => 'good', 'location' => 'Lemari B-02'],
            ['code' => 'TL-003', 'name' => 'Micrometer 0-25mm', 'category' => 'Measuring', 'total_quantity' => 6, 'available_quantity' => 6, 'condition' => 'good', 'location' => 'Lemari B-01'],
            ['code' => 'TL-004', 'name' => 'Vernier Caliper 150mm', 'category' => 'Measuring', 'total_quantity' => 10, 'available_quantity' => 10, 'condition' => 'good', 'location' => 'Lemari B-01'],
            ['code' => 'TL-005', 'name' => 'Bor Tangan', 'category' => 'Power Tools', 'total_quantity' => 4, 'available_quantity' => 4, 'condition' => 'good', 'location' => 'Lemari C-01'],
            ['code' => 'TL-006', 'name' => 'Grinder Tangan', 'category' => 'Power Tools', 'total_quantity' => 6, 'available_quantity' => 6, 'condition' => 'fair', 'location' => 'Lemari C-02'],
            ['code' => 'TL-007', 'name' => 'Kikir Set', 'category' => 'Hand Tools', 'total_quantity' => 10, 'available_quantity' => 10, 'condition' => 'good', 'location' => 'Lemari A-02'],
            ['code' => 'TL-008', 'name' => 'Palu Karet', 'category' => 'Hand Tools', 'total_quantity' => 8, 'available_quantity' => 8, 'condition' => 'good', 'location' => 'Lemari A-01'],
        ];
        foreach ($tools as $tool) {
            Tool::firstOrCreate(['code' => $tool['code']], $tool);
        }

        $this->command->info('Master data seeded successfully!');
    }
}
