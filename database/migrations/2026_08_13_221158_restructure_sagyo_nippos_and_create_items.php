<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // To avoid foreign key constraint errors during restructure, we can just clear existing data
        // since this is still in development
        DB::table('sagyo_nippos')->truncate();

        Schema::table('sagyo_nippos', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['mold_id']);
            $table->dropForeign(['job_code_id']);
            $table->dropForeign(['part_code_id']);
            $table->dropColumn(['project_id', 'mold_id', 'job_code_id', 'part_code_id', 'hours', 'notes']);
            
            $table->decimal('total_hours', 8, 2)->default(0)->after('date');
        });

        Schema::create('sagyo_nippo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sagyo_nippo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mold_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_code_id')->constrained()->cascadeOnDelete();
            $table->decimal('hours', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sagyo_nippo_items');

        Schema::table('sagyo_nippos', function (Blueprint $table) {
            $table->dropColumn('total_hours');
            
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mold_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_code_id')->constrained()->cascadeOnDelete();
            $table->decimal('hours', 8, 2)->default(0);
            $table->text('notes')->nullable();
        });
    }
};
