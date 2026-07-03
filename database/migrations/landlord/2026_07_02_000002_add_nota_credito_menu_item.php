<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega "Notas de Crédito" como item del menú Ventas (sibling de "Comprobante Venta"/
 * "Clientes", mismo permiso ventas.ver). Vive en landlord (master); el backfill tenant
 * sincroniza esta fila hacia cada tenant existente (module_children es una COPIA por tenant).
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::connection('landlord')->table('module_children')
            ->where('route_name', 'ventas.nota_credito.index')
            ->exists();

        if ($exists) {
            return;
        }

        $saleModuleId = DB::connection('landlord')->table('modules')
            ->where('description', 'Ventas')
            ->value('id');

        if (!$saleModuleId) {
            return;
        }

        DB::connection('landlord')->table('module_children')->insert([
            'module_id'   => $saleModuleId,
            'description' => 'Notas de Crédito',
            'route_name'  => 'ventas.nota_credito.index',
            'permission'  => 'ventas.ver',
            'order'       => 3,
            'show'        => 'tenant',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::connection('landlord')->table('module_children')
            ->where('route_name', 'ventas.nota_credito.index')
            ->delete();
    }
};
