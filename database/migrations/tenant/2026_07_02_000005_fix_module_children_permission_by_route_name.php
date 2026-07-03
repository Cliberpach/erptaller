<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige el mapeo permission de module_children/module_grand_children en tenants viejos.
 *
 * El backfill anterior (2026_07_01_130000) sincronizaba `permission` matcheando por ID
 * contra landlord. Pero landlord agregó módulos nuevos DESPUÉS de que este tenant fuera
 * provisionado (ej. "Configuración"), corriendo los ids de TODO lo que viene después ->
 * el backfill por id terminó pegando el permiso de una fila en la fila con el MISMO id
 * pero de OTRO nodo (ej. "Comprobante Venta" quedó con el permiso de otro nodo).
 *
 * Fix: matchear por route_name (estable entre landlord y tenant, es el nombre real de
 * ruta) en vez de id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('module_children')->count() === 0) {
            return;
        }

        $children = DB::connection('landlord')->table('module_children')
            ->whereNotNull('route_name')
            ->get(['route_name', 'permission']);

        foreach ($children as $c) {
            DB::table('module_children')
                ->where('route_name', $c->route_name)
                ->update(['permission' => $c->permission]);
        }

        $grands = DB::connection('landlord')->table('module_grand_children')
            ->whereNotNull('route_name')
            ->get(['route_name', 'permission']);

        foreach ($grands as $g) {
            DB::table('module_grand_children')
                ->where('route_name', $g->route_name)
                ->update(['permission' => $g->permission]);
        }
    }

    public function down(): void
    {
        // No hay forma segura de revertir a los valores corruptos previos; no-op.
    }
};
