<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 30)->unique()->comment('Nomor PO, misal: PO-2024-0001');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'sent', 'ordered', 'partial', 'arrived', 'closed', 'cancelled'])->default('draft');
            $table->date('po_date');
            $table->date('expected_arrival_date')->nullable();
            $table->date('actual_arrival_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->string('payment_terms')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('invoice_file')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Purchase Order Items
        Schema::create('po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('components')->onDelete('restrict');
            $table->integer('qty_ordered');
            $table->integer('qty_received')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('unit')->default('pcs');
            $table->text('specifications')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_items');
        Schema::dropIfExists('purchase_orders');
    }
};
