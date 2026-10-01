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
        Schema::table('maintenances', function (Blueprint $table) {
            $table->text('temporary_action')->nullable();
            $table->text('rca_man')->nullable();
            $table->text('rca_machine')->nullable();
            $table->text('rca_material')->nullable();
            $table->text('rca_method')->nullable();
            $table->text('permanent_countermeasure')->nullable();
            $table->text('replaced_parts_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('photo_after')->nullable();
            $table->dateTime('target_due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'temporary_action',
                'rca_man',
                'rca_machine',
                'rca_material',
                'rca_method',
                'permanent_countermeasure',
                'replaced_parts_note',
                'verified_by',
                'verified_at',
                'photo_after',
                'target_due_date',
            ]);
        });
    }
};
