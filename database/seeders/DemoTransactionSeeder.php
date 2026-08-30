<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\Machine;
use App\Models\Maintenance;
use App\Models\Mold;
use App\Models\PurchaseOrder;
use App\Models\PoItem;
use App\Models\StockMovement;
use App\Models\Tool;
use App\Models\ToolLoan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@moldmaster.id')->first();
        $leader = User::where('email', 'leader@moldmaster.id')->first();
        $operator = User::where('email', 'operator@moldmaster.id')->first();
        $operator2 = User::where('email', 'operator2@moldmaster.id')->first();

        $components = Component::all()->keyBy('code');
        $machines = Machine::all()->keyBy('code');
        $molds = Mold::all()->keyBy('code');
        $vendors = \App\Models\Vendor::all()->keyBy('code');
        $tools = Tool::all()->keyBy('code');

        // ===================== PURCHASE ORDERS =====================
        $po1 = PurchaseOrder::create([
            'vendor_id' => $vendors['VND-001']->id,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'status' => 'arrived',
            'po_date' => now()->subDays(30),
            'expected_arrival_date' => now()->subDays(23),
            'actual_arrival_date' => now()->subDays(22),
            'currency' => 'IDR',
            'payment_terms' => 'Net 30',
            'notes' => 'Order rutin komponen bumper Avanza',
        ]);
        PoItem::create([
            'purchase_order_id' => $po1->id,
            'component_id' => $components['COMP-INS-001']->id,
            'qty_ordered' => 4, 'qty_received' => 4,
            'unit_price' => 2500000, 'unit' => 'pcs',
        ]);
        PoItem::create([
            'purchase_order_id' => $po1->id,
            'component_id' => $components['COMP-EJT-001']->id,
            'qty_ordered' => 20, 'qty_received' => 20,
            'unit_price' => 85000, 'unit' => 'pcs',
        ]);

        $po2 = PurchaseOrder::create([
            'vendor_id' => $vendors['VND-002']->id,
            'created_by' => $admin->id,
            'status' => 'ordered',
            'po_date' => now()->subDays(5),
            'expected_arrival_date' => now()->addDays(9),
            'currency' => 'IDR',
            'payment_terms' => 'Net 30',
            'notes' => 'Order ejector pin untuk mold Innova',
        ]);
        PoItem::create([
            'purchase_order_id' => $po2->id,
            'component_id' => $components['COMP-INS-004']->id,
            'qty_ordered' => 2, 'qty_received' => 0,
            'unit_price' => 4500000, 'unit' => 'pcs',
        ]);

        // ===================== STOCK MOVEMENTS =====================
        // Approved outgoing movement
        StockMovement::create([
            'reference_number' => 'OUT-' . now()->format('Ymd') . '-A1B2C',
            'component_id' => $components['COMP-EJT-001']->id,
            'mold_id' => $molds['MOL-2024-001']->id,
            'machine_id' => $machines['CNC-001']->id,
            'requested_by' => $operator->id,
            'approved_by' => $leader->id,
            'type' => 'out',
            'status' => 'approved',
            'quantity' => 5,
            'quantity_before' => 25,
            'quantity_after' => 20,
            'purpose' => 'Penggantian ejector pin yang bengkok karena overrun',
            'operator_name' => 'Operator Satu',
            'notes' => 'Ejector pin no. 3 dan 7 bengkok',
            'approved_at' => now()->subDays(3),
        ]);

        // Pending approval
        StockMovement::create([
            'reference_number' => 'OUT-' . now()->format('Ymd') . '-D3E4F',
            'component_id' => $components['COMP-SPR-001']->id,
            'mold_id' => $molds['MOL-2024-001']->id,
            'machine_id' => $machines['CNC-001']->id,
            'requested_by' => $operator->id,
            'type' => 'out',
            'status' => 'pending',
            'quantity' => 10,
            'purpose' => 'Penggantian spring return yang lemah',
            'operator_name' => 'Operator Satu',
        ]);

        // Return movement
        StockMovement::create([
            'reference_number' => 'RET-' . now()->format('Ymd') . '-G5H6I',
            'component_id' => $components['COMP-EJT-002']->id,
            'mold_id' => $molds['MOL-2024-001']->id,
            'requested_by' => $operator->id,
            'approved_by' => $leader->id,
            'type' => 'return',
            'status' => 'approved',
            'quantity' => 3,
            'quantity_before' => 12,
            'quantity_after' => 15,
            'condition' => 'good',
            'purpose' => 'Produksi selesai, komponen dikembalikan',
            'operator_name' => 'Operator Satu',
            'approved_at' => now()->subDays(1),
        ]);

        // ===================== MAINTENANCES =====================
        Maintenance::create([
            'machine_id' => $machines['EDM-002']->id,
            'reported_by' => $operator2->id,
            'approved_by' => $leader->id,
            'type' => 'breakdown',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'problem_description' => 'Mesin tidak bisa start, display error E-001 (servo drive fault)',
            'action_taken' => 'Teknisi sedang periksa servo drive unit',
            'reported_at' => now()->subDays(2),
            'approved_at' => now()->subDays(2)->addHours(2),
            'started_at' => now()->subDays(2)->addHours(3),
            'downtime_hours' => 48.0,
            'labor_cost' => 500000,
        ]);

        Maintenance::create([
            'machine_id' => $machines['GRD-002']->id,
            'reported_by' => $operator->id,
            'type' => 'breakdown',
            'status' => 'pending',
            'priority' => 'high',
            'problem_description' => 'Spindle motor mengeluarkan suara kasar, getaran berlebihan',
            'reported_at' => now()->subHours(6),
        ]);

        Maintenance::create([
            'machine_id' => $machines['CNC-001']->id,
            'reported_by' => $admin->id,
            'approved_by' => $admin->id,
            'type' => 'preventive',
            'status' => 'completed',
            'priority' => 'medium',
            'problem_description' => 'Preventive maintenance rutin 3 bulanan',
            'action_taken' => 'Ganti oli spindle, bersihkan coolant filter, kalibrasi akurasi sumbu XYZ',
            'reported_at' => now()->subDays(15),
            'approved_at' => now()->subDays(15),
            'started_at' => now()->subDays(15)->addHours(1),
            'completed_at' => now()->subDays(14),
            'downtime_hours' => 8.0,
            'labor_cost' => 300000,
            'spare_parts_cost' => 450000,
            'total_cost' => 750000,
        ]);

        // ===================== TOOL LOANS =====================
        ToolLoan::create([
            'loan_number' => 'LOAN-' . now()->format('Ymd') . '-AA01',
            'tool_id' => $tools['TL-002']->id,
            'borrower_id' => $operator->id,
            'approved_by' => $leader->id,
            'quantity' => 2,
            'status' => 'borrowed',
            'purpose' => 'Ukur kerataan permukaan insert cavity',
            'planned_return_date' => now()->addDays(2),
            'borrowed_at' => now()->subDays(1),
        ]);

        ToolLoan::create([
            'loan_number' => 'LOAN-' . now()->format('Ymd') . '-BB02',
            'tool_id' => $tools['TL-006']->id,
            'borrower_id' => $operator2->id,
            'quantity' => 1,
            'status' => 'pending',
            'purpose' => 'Finishing permukaan core mold Brio',
            'planned_return_date' => now()->addDays(1),
        ]);

        $this->command->info('Demo transactions seeded successfully!');
    }
}
