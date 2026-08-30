<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Maintenance Records
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number', 30)->unique()->nullable();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('restrict');
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type', ['preventive', 'corrective', 'breakdown', 'inspection']);
            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->enum('priority', ['urgent', 'high', 'medium', 'low'])->default('medium');
            $table->string('problem_description');
            $table->text('action_taken')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('downtime_hours', 8, 2)->nullable();
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('spare_parts_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'status']);
            $table->index(['status', 'priority']);
        });

        // Maintenance Spare Parts
        Schema::create('maintenance_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->onDelete('cascade');
            $table->string('part_name');
            $table->string('part_number')->nullable();
            $table->integer('quantity');
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('vendor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_spare_parts');
        Schema::dropIfExists('maintenances');
    }
};
