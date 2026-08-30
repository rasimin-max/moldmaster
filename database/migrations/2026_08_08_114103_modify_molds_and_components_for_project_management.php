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
        Schema::table('molds', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->nullOnDelete();
            $table->string('photo')->nullable()->after('description');
        });

        Schema::table('components', function (Blueprint $table) {
            $table->integer('required_qty')->default(1)->after('stock');
            $table->string('heat_treatment')->nullable()->after('size_spec');
            $table->text('remarks')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn(['required_qty', 'heat_treatment', 'remarks']);
        });

        Schema::table('molds', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'photo']);
        });
    }
};
