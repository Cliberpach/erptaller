<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_stock;
            CREATE FUNCTION fn_stock(p_almacen_id INT, p_producto_id INT, p_fecha DATE)
            RETURNS DECIMAL(16,6)
            DETERMINISTIC
            BEGIN
                DECLARE p_stock DECIMAL(16,6) DEFAULT 0;

                SELECT
                    COALESCE(SUM(cantidad_entrada) - SUM(cantidad_salida), 0)
                INTO p_stock
                FROM (

                    -- COMPRAS
                    SELECT
                        CASE
                            WHEN k.type = 'ENTRADA' THEN k.quantity
                            ELSE 0
                        END AS cantidad_entrada,

                        CASE
                            WHEN k.type = 'SALIDA' THEN k.quantity
                            ELSE 0
                        END AS cantidad_salida
                    FROM kardex AS k
                    WHERE k.warehouse_id = p_almacen_id
                    AND k.product_id = p_producto_id
                    AND DATE(k.date) < p_fecha

                ) AS t;

                RETURN p_stock;
            END;
        ");
    }

    public function down() {
        DB::unprepared("DROP FUNCTION IF EXISTS fn_stock;");
    }
};
