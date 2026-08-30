<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('Kode komponen unik');
            $table->string('qr_code', 100)->unique()->nullable()->comment('QR code string');
            $table->string('name');
            $table->foreignId('category_id')->constrained('component_categories')->onDelete('restrict');
            $table->foreignId('mold_id')->nullable()->constrained('molds')->onDelete('set null');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->string('material')->nullable()->comment('Material bahan komponen');
            $table->string('size_spec')->nullable()->comment('Spesifikasi ukuran');
            $table->string('rack_location')->nullable()->comment('Lokasi rak penyimpanan, misal: R01-A-03');
            $table->integer('stock')->default(0);
            $table->integer('stock_minimum')->default(5);
            $table->integer('stock_reserved')->default(0)->comment('Stok yg sedang dipinjam/dipakai');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->string('unit')->default('pcs');
            $table->integer('shot_count')->default(0)->comment('Jumlah shot dipakai');
            $table->integer('shot_life')->nullable()->comment('Maks shot sebelum perlu replacement');
            $table->string('photo')->nullable();
            $table->string('qr_image')->nullable()->comment('Path file gambar QR code');
            $table->enum('status', ['ready', 'in_use', 'pending_arrival', 'maintenance', 'retired'])->default('pending_arrival');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category_id']);
            $table->index(['mold_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
