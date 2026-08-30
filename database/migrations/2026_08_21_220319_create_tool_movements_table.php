<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tool_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->unique();
            $table->foreignId('tool_id')->constrained('tools')->onDelete('restrict');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->integer('quantity');
            $table->integer('quantity_before')->nullable();
            $table->integer('quantity_after')->nullable();
            $table->string('purpose')->nullable();
            $table->string('operator_name')->nullable();
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['tool_id', 'type', 'status']);
            $table->index(['requested_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_movements');
    }
};
