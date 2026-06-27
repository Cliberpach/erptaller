<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El código (SaleDetailService, SaleDto) escribe net_quantity y el dashboard la lee
     * (SUM como cantidad_vendida + utilidad), pero ninguna migración la creaba -> "Unknown
     * column 'net_quantity'" rompía TODA venta al insertar el detalle. Columna agregada a mano
     * en prod, nunca capturada como migración (mismo patrón que unit_id).
     *
     * Espejo de quantity: decimal(10,2) unsigned. Nullable para que el ALTER no falle sobre
     * filas existentes; el código siempre la setea (= quantity) en el insert.
     */
    public function up(): void
    {
        Schema::table('sales_documents_details', function (Blueprint $table) {
            $table->decimal('net_quantity', 10, 2)->unsigned()->nullable()->after('quantity');
        });

        // Backfill: filas existentes = quantity (no 0; el dashboard la suma como cantidad vendida).
        DB::table('sales_documents_details')->update(['net_quantity' => DB::raw('quantity')]);
    }

    public function down(): void
    {
        Schema::table('sales_documents_details', function (Blueprint $table) {
            $table->dropColumn('net_quantity');
        });
    }
};
