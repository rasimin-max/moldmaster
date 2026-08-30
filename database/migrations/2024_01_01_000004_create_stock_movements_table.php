<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->unique()->comment('Nomor referensi transaksi');
            $table->foreignId('component_id')->constrained('components')->onDelete('restrict');
            $table->foreignId('mold_id')->nullable()->constrained('molds')->onDelete('set null');
            $table->foreignId('machine_id')->nullable()->constrained('machines')->onDelete('set null');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type', ['in', 'out', 'return', 'adjustment', 'opname']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->integer('quantity');
            $table->integer('quantity_before')->nullable()->comment('Stok sebelum transaksi');
            $table->integer('quantity_after')->nullable()->comment('Stok setelah transaksi');
            $table->enum('condition', ['good', 'damaged', 'needs_sharpening', 'needs_coating', 'lost'])->nullable()->comment('Kondisi saat return/masuk');
            $table->string('purpose')->nullable()->comment('Keperluan penggunaan');
            $table->string('operator_name')->nullable()->comment('Nama operator yang memakai');
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('source_po_id')->nullable()->constrained('purchase_orders')->onDelete('set null');
            $table->timestamps();

            $table->index(['component_id', 'type', 'status']);
            $table->index(['requested_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
