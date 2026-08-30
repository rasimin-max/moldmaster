<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Mold;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComponentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ComponentCategory::pluck('id', 'slug');
        $molds = Mold::pluck('id', 'code');
        $vendors = Vendor::pluck('id', 'code');

        $components = [
            // Bumper Avanza Mold Components
            ['code' => 'COMP-INS-001', 'name' => 'Insert Cavity Bumper Front L', 'category_slug' => 'insert', 'mold_code' => 'MOL-2024-001', 'vendor_code' => 'VND-001', 'material' => 'NAK80', 'size_spec' => '250x180x120mm', 'rack_location' => 'R01-A-01', 'stock' => 4, 'stock_minimum' => 2, 'unit_price' => 2500000, 'shot_count' => 125000, 'shot_life' => 500000, 'status' => 'ready'],
            ['code' => 'COMP-INS-002', 'name' => 'Insert Cavity Bumper Front R', 'category_slug' => 'insert', 'mold_code' => 'MOL-2024-001', 'vendor_code' => 'VND-001', 'material' => 'NAK80', 'size_spec' => '250x180x120mm', 'rack_location' => 'R01-A-02', 'stock' => 4, 'stock_minimum' => 2, 'unit_price' => 2500000, 'shot_count' => 125000, 'shot_life' => 500000, 'status' => 'ready'],
            ['code' => 'COMP-EJT-001', 'name' => 'Ejector Pin D8 L200', 'category_slug' => 'ejector', 'mold_code' => 'MOL-2024-001', 'vendor_code' => 'VND-002', 'material' => 'SKD61', 'size_spec' => 'D8x200mm', 'rack_location' => 'R01-B-01', 'stock' => 20, 'stock_minimum' => 10, 'unit_price' => 85000, 'shot_count' => 125000, 'shot_life' => 200000, 'status' => 'ready'],
            ['code' => 'COMP-EJT-002', 'name' => 'Ejector Pin D10 L250', 'category_slug' => 'ejector', 'mold_code' => 'MOL-2024-001', 'vendor_code' => 'VND-002', 'material' => 'SKD61', 'size_spec' => 'D10x250mm', 'rack_location' => 'R01-B-02', 'stock' => 15, 'stock_minimum' => 8, 'unit_price' => 95000, 'shot_count' => 125000, 'shot_life' => 200000, 'status' => 'ready'],
            ['code' => 'COMP-SPR-001', 'name' => 'Spring Return D40 L80', 'category_slug' => 'spring', 'mold_code' => 'MOL-2024-001', 'vendor_code' => 'VND-003', 'material' => 'Chrome Vanadium', 'size_spec' => 'D40x80mm', 'rack_location' => 'R01-C-01', 'stock' => 30, 'stock_minimum' => 15, 'unit_price' => 45000, 'shot_count' => 100000, 'shot_life' => 150000, 'status' => 'ready'],
            ['code' => 'COMP-GDE-001', 'name' => 'Guide Pin D25 L150', 'category_slug' => 'guide', 'mold_code' => 'MOL-2024-001', 'vendor_code' => 'VND-001', 'material' => 'SKS3', 'size_spec' => 'D25x150mm', 'rack_location' => 'R01-D-01', 'stock' => 8, 'stock_minimum' => 4, 'unit_price' => 180000, 'shot_count' => 125000, 'shot_life' => 500000, 'status' => 'ready'],
            // Grille Brio Components
            ['code' => 'COMP-INS-003', 'name' => 'Insert Core Grille Upper', 'category_slug' => 'insert', 'mold_code' => 'MOL-2024-002', 'vendor_code' => 'VND-001', 'material' => 'S136', 'size_spec' => '300x200x80mm', 'rack_location' => 'R02-A-01', 'stock' => 2, 'stock_minimum' => 2, 'unit_price' => 3200000, 'shot_count' => 87500, 'shot_life' => 300000, 'status' => 'in_use'],
            ['code' => 'COMP-COR-001', 'name' => 'Core Grille Lower', 'category_slug' => 'core', 'mold_code' => 'MOL-2024-002', 'vendor_code' => 'VND-001', 'material' => 'S136', 'size_spec' => '280x150x60mm', 'rack_location' => 'R02-A-02', 'stock' => 3, 'stock_minimum' => 2, 'unit_price' => 2800000, 'shot_count' => 87500, 'shot_life' => 300000, 'status' => 'ready'],
            ['code' => 'COMP-SLD-001', 'name' => 'Slide Unit L Grille', 'category_slug' => 'slide', 'mold_code' => 'MOL-2024-002', 'vendor_code' => 'VND-002', 'material' => 'P20', 'size_spec' => '120x80x50mm', 'rack_location' => 'R02-B-01', 'stock' => 2, 'stock_minimum' => 1, 'unit_price' => 1500000, 'shot_count' => 50000, 'shot_life' => 300000, 'status' => 'ready'],
            ['code' => 'COMP-EJT-003', 'name' => 'Ejector Plate Grille', 'category_slug' => 'ejector', 'mold_code' => 'MOL-2024-002', 'vendor_code' => 'VND-002', 'material' => 'S45C', 'size_spec' => '400x300x25mm', 'rack_location' => 'R02-B-02', 'stock' => 2, 'stock_minimum' => 1, 'unit_price' => 850000, 'shot_count' => 87500, 'shot_life' => 300000, 'status' => 'ready'],
            // Xpander Components
            ['code' => 'COMP-PIN-001', 'name' => 'Return Pin D20 L180', 'category_slug' => 'pin', 'mold_code' => 'MOL-2024-003', 'vendor_code' => 'VND-004', 'material' => 'SKD11', 'size_spec' => 'D20x180mm', 'rack_location' => 'R03-A-01', 'stock' => 12, 'stock_minimum' => 6, 'unit_price' => 125000, 'shot_count' => 200000, 'shot_life' => 400000, 'status' => 'ready'],
            ['code' => 'COMP-BLT-001', 'name' => 'Bolt M12x50 Class 12.9', 'category_slug' => 'bolt', 'mold_code' => 'MOL-2024-003', 'vendor_code' => 'VND-004', 'material' => 'Alloy Steel', 'size_spec' => 'M12x50mm', 'rack_location' => 'R03-B-01', 'stock' => 100, 'stock_minimum' => 50, 'unit_price' => 12000, 'shot_count' => 0, 'shot_life' => null, 'status' => 'ready'],
            ['code' => 'COMP-BLT-002', 'name' => 'Bolt M16x60 Class 12.9', 'category_slug' => 'bolt', 'mold_code' => 'MOL-2024-003', 'vendor_code' => 'VND-004', 'material' => 'Alloy Steel', 'size_spec' => 'M16x60mm', 'rack_location' => 'R03-B-02', 'stock' => 80, 'stock_minimum' => 40, 'unit_price' => 18000, 'shot_count' => 0, 'shot_life' => null, 'status' => 'ready'],
            // Low stock / pending items
            ['code' => 'COMP-EJT-004', 'name' => 'Ejector Pin D6 L150 (Stok Menipis)', 'category_slug' => 'ejector', 'mold_code' => 'MOL-2023-005', 'vendor_code' => 'VND-002', 'material' => 'SKD61', 'size_spec' => 'D6x150mm', 'rack_location' => 'R04-A-01', 'stock' => 3, 'stock_minimum' => 10, 'unit_price' => 75000, 'shot_count' => 350000, 'shot_life' => 200000, 'status' => 'ready'],
            ['code' => 'COMP-INS-004', 'name' => 'Insert Cavity Innova (On Order)', 'category_slug' => 'insert', 'mold_code' => 'MOL-2023-005', 'vendor_code' => 'VND-001', 'material' => 'NAK80', 'size_spec' => '350x250x150mm', 'rack_location' => null, 'stock' => 0, 'stock_minimum' => 2, 'unit_price' => 4500000, 'shot_count' => 0, 'shot_life' => 500000, 'status' => 'pending_arrival'],
            ['code' => 'COMP-SPR-002', 'name' => 'Spring D50 L100 (Stok Menipis)', 'category_slug' => 'spring', 'mold_code' => 'MOL-2024-001', 'vendor_code' => 'VND-003', 'material' => 'Chrome Vanadium', 'size_spec' => 'D50x100mm', 'rack_location' => 'R01-C-02', 'stock' => 2, 'stock_minimum' => 10, 'unit_price' => 55000, 'shot_count' => 80000, 'shot_life' => 150000, 'status' => 'ready'],
        ];

        foreach ($components as $componentData) {
            $categorySlug = $componentData['category_slug'];
            $moldCode = $componentData['mold_code'];
            $vendorCode = $componentData['vendor_code'];
            unset($componentData['category_slug'], $componentData['mold_code'], $componentData['vendor_code']);

            $componentData['category_id'] = $categories[$categorySlug] ?? null;
            $componentData['mold_id'] = $molds[$moldCode] ?? null;
            $componentData['vendor_id'] = $vendors[$vendorCode] ?? null;
            $componentData['qr_code'] = 'QR-' . strtoupper(Str::random(10));

            Component::firstOrCreate(['code' => $componentData['code']], $componentData);
        }

        $this->command->info('Components seeded successfully!');
    }
}
