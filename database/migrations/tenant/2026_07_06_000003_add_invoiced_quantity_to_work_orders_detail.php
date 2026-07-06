<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reemplaza el flag booleano `invoiced` por un contador acumulado
 * `invoiced_quantity` (patrón estándar de facturación parcial: Odoo
 * qty_invoiced, SAP "billed quantity"). Permite facturar una línea en
 * varias partes sin perder el rastro de cuánto ya se facturó, y sin
 * bloquear/perder ese rastro cuando se edita la OT (ver docs/PLAN_OT_INVOICE.md).
 *
 * `invoiced` se conserva por compatibilidad (no se lee más en la lógica
 * nueva) hasta una limpieza posterior.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders_products', function (Blueprint $table) {
            $table->decimal('invoiced_quantity', 16, 6)->unsigned()->default(0)->after('invoiced');
        });

        Schema::table('work_orders_services', function (Blueprint $table) {
            $table->decimal('invoiced_quantity', 16, 6)->unsigned()->default(0)->after('invoiced');
        });

        // Backfill: lo que ya estaba marcado invoiced=true se considera
        // facturado en su cantidad completa (única forma en que se facturaba
        // hasta ahora: todo o nada por línea).
        DB::table('work_orders_products')
            ->where('invoiced', true)
            ->update(['invoiced_quantity' => DB::raw('quantity')]);

        DB::table('work_orders_services')
            ->where('invoiced', true)
            ->update(['invoiced_quantity' => DB::raw('quantity')]);
    }

    public function down(): void
    {
        Schema::table('work_orders_products', function (Blueprint $table) {
            $table->dropColumn('invoiced_quantity');
        });

        Schema::table('work_orders_services', function (Blueprint $table) {
            $table->dropColumn('invoiced_quantity');
        });
    }
};
