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
        Schema::table('machine_programs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mold_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('component_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('programmer')->nullable();
            
            $table->string('r_f', 10)->nullable();
            $table->string('b', 10)->nullable();
            $table->string('tool_no', 50)->nullable();
            $table->string('tool_name')->nullable();
            $table->decimal('tool_dia', 8, 2)->nullable();
            $table->decimal('tool_r', 8, 2)->nullable();
            $table->decimal('tool_length_total', 8, 2)->nullable();
            $table->decimal('tool_length_eff', 8, 2)->nullable();
            $table->string('tool_num', 20)->nullable();
            $table->string('holder')->nullable();
            $table->decimal('ps_thick', 8, 2)->nullable();
            $table->integer('rpm')->nullable();
            $table->integer('feed')->nullable();
            $table->decimal('doc', 8, 2)->nullable();
            $table->string('setting')->nullable();
            $table->string('barcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_programs', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['mold_id']);
            $table->dropForeign(['component_id']);
            $table->dropForeign(['machine_id']);
            
            $table->dropColumn([
                'project_id', 'mold_id', 'component_id', 'machine_id', 'programmer',
                'r_f', 'b', 'tool_no', 'tool_name', 'tool_dia', 'tool_r',
                'tool_length_total', 'tool_length_eff', 'tool_num', 'holder',
                'ps_thick', 'rpm', 'feed', 'doc', 'setting', 'barcode'
            ]);
        });
    }
};
