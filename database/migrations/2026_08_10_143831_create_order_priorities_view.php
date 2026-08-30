<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW order_priorities AS
            SELECT 
                CONCAT('C-', c.id) as id,
                'component' AS type,
                c.id AS reference_id,
                c.code AS item_code,
                c.name AS item_name,
                cat.name AS category,
                c.stock AS current_stock,
                c.stock_minimum AS min_stock,
                GREATEST(0, c.stock_minimum - c.stock) AS order_qty,
                'Safety Stock (Part Common)' AS reason
            FROM components c
            JOIN component_categories cat ON c.category_id = cat.id
            WHERE UPPER(cat.name) LIKE '%COMMON%' AND c.stock <= c.stock_minimum

            UNION ALL

            SELECT 
                CONCAT('T-', t.id) as id,
                'tool' AS type,
                t.id AS reference_id,
                t.code AS item_code,
                t.name AS item_name,
                t.category AS category,
                t.total_quantity AS current_stock,
                t.min_stock AS min_stock,
                GREATEST(0, t.min_stock - t.total_quantity) AS order_qty,
                'Safety Stock (Tool)' AS reason
            FROM tools t
            WHERE t.total_quantity <= t.min_stock

            UNION ALL

            SELECT 
                CONCAT('P-', c.id) as id,
                'project_component' AS type,
                c.id AS reference_id,
                c.code AS item_code,
                c.name AS item_name,
                cat.name AS category,
                c.stock AS current_stock,
                0 AS min_stock,
                GREATEST(0, c.required_qty - c.stock) AS order_qty,
                'Kekurangan Project (Prioritas)' AS reason
            FROM components c
            JOIN component_categories cat ON c.category_id = cat.id
            WHERE UPPER(cat.name) NOT LIKE '%COMMON%' AND c.stock < c.required_qty;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_priorities_view');
    }
};
