<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vendors / Suppliers
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('pic_name')->nullable()->comment('Person in Charge');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->integer('lead_time_days')->default(7)->comment('Estimasi hari pengiriman');
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Molds
        Schema::create('molds', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('Kode mold, misal: MOL-2024-001');
            $table->string('name');
            $table->string('project_name')->nullable();
            $table->string('customer')->nullable();
            $table->string('product_type')->nullable()->comment('Bumper, Grille, dll');
            $table->integer('cavity')->default(1);
            $table->integer('shot_life')->nullable()->comment('Target umur shot');
            $table->integer('current_shot')->default(0);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'retired'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Component Categories
        Schema::create('component_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1')->comment('Hex color for badge');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Machines
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->enum('type', ['CNC', 'EDM', 'Wirecut', 'Grinding', 'Milling', 'Lathe', 'Drilling', 'Polishing', 'Assembly', 'Laser']);
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('area')->nullable()->comment('Area/lokasi mesin');
            $table->year('year_purchased')->nullable();
            $table->enum('status', ['operational', 'maintenance', 'breakdown', 'idle', 'retired'])->default('operational');
            $table->decimal('hourly_rate', 10, 2)->default(0)->comment('Biaya per jam mesin');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tools (Alat Kerja)
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->integer('total_quantity')->default(1);
            $table->integer('available_quantity')->default(1);
            $table->enum('condition', ['good', 'fair', 'poor', 'damaged'])->default('good');
            $table->string('location')->nullable()->comment('Lokasi penyimpanan alat');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
        Schema::dropIfExists('machines');
        Schema::dropIfExists('component_categories');
        Schema::dropIfExists('molds');
        Schema::dropIfExists('vendors');
    }
};
