<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tool Loans (Peminjaman Alat)
        Schema::create('tool_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 30)->unique();
            $table->foreignId('tool_id')->constrained('tools')->onDelete('restrict');
            $table->foreignId('borrower_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->enum('status', ['pending', 'approved', 'borrowed', 'returned', 'rejected', 'overdue'])->default('pending');
            $table->string('purpose')->nullable();
            $table->date('planned_return_date')->nullable();
            $table->timestamp('borrowed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('condition_borrowed', ['good', 'fair', 'poor'])->nullable();
            $table->enum('condition_returned', ['good', 'fair', 'poor', 'damaged'])->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['borrower_id', 'status']);
            $table->index(['tool_id', 'status']);
        });

        // Stock Opnames
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number', 30)->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['in_progress', 'completed', 'approved', 'cancelled'])->default('in_progress');
            $table->date('opname_date');
            $table->string('area')->nullable()->comment('Area yang diopname');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Stock Opname Items
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('components')->onDelete('restrict');
            $table->integer('system_stock')->comment('Stok menurut sistem');
            $table->integer('physical_stock')->comment('Stok fisik hasil hitung');
            $table->integer('difference')->comment('Selisih: fisik - sistem');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable()->comment('Snapshot nama user saat log dibuat');
            $table->string('action', 50)->comment('created, updated, deleted, approved, rejected, login, logout');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });

        // System Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string')->comment('string, integer, boolean, json');
            $table->string('group', 50)->default('general');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('tool_loans');
    }
};
