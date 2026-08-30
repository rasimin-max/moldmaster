<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make category_id nullable first
        Schema::table('components', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
        });

        // Kosongkan data kategori lama
        DB::table('components')->update(['category_id' => null]);
        Schema::disableForeignKeyConstraints();
        DB::table('component_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        // Sederhanakan tabel component_categories: hapus slug, color, description
        Schema::table('component_categories', function (Blueprint $table) {
            if (Schema::hasColumn('component_categories', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('component_categories', 'color')) {
                $table->dropColumn('color');
            }
            if (Schema::hasColumn('component_categories', 'description')) {
                $table->dropColumn('description');
            }
        });

        // Tabel baru: Material
        Schema::create('material_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Tabel baru: Machining
        Schema::create('machining_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Tambah kolom relasi di components
        Schema::table('components', function (Blueprint $table) {
            $table->foreignId('material_type_id')->nullable()->after('category_id')->constrained('material_types')->nullOnDelete();
            $table->foreignId('machining_type_id')->nullable()->after('material_type_id')->constrained('machining_types')->nullOnDelete();
        });

        // Isi data awal Material
        DB::table('material_types')->insert([
            ['name' => 'Outhouse', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Inhouse', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Isi data awal Machining
        DB::table('machining_types')->insert([
            ['name' => 'SNK', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EDM', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wirecut', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bubut', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropForeign(['material_type_id']);
            $table->dropForeign(['machining_type_id']);
            $table->dropColumn(['material_type_id', 'machining_type_id']);
        });
        Schema::dropIfExists('material_types');
        Schema::dropIfExists('machining_types');
    }
};
