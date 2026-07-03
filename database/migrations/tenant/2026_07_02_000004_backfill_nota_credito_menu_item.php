<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sincroniza el nuevo item de menú "Notas de Crédito" (agregado en landlord) hacia el
 * tenant. module_children es una COPIA por tenant (ver TenantModuleSeeder) — un item
 * nuevo en landlord no aparece solo, hay que insertarlo acá también.
 *
 * OJO: `modules.id` NO está garantizado igual entre landlord y un tenant ya provisionado
 * (si landlord agregó un módulo nuevo DESPUÉS de crear el tenant, todos los ids de ese
 * tenant quedan corridos respecto a landlord). Por eso el module_id se resuelve acá por
 * DESCRIPCIÓN dentro del propio tenant, nunca copiando el id crudo de landlord.
 *
 * GUARDA de ordering: en un tenant NUEVO las migraciones corren ANTES del seeder (module_children
 * vacío) -> se saltea, lo puebla el TenantModuleSeeder ya actualizado.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('module_children')->count() === 0) {
            return;
        }

        $exists = DB::table('module_children')
            ->where('route_name', 'ventas.nota_credito.index')
            ->exists();

        if ($exists) {
            return;
        }

        $source = DB::connection('landlord')->table('module_children')
            ->where('route_name', 'ventas.nota_credito.index')
            ->first();

        if (!$source) {
            return;
        }

        $sourceModuleDescription = DB::connection('landlord')->table('modules')
            ->where('id', $source->module_id)
            ->value('description');

        $tenantModuleId = DB::table('modules')
            ->where('description', $sourceModuleDescription)
            ->value('id');

        if (!$tenantModuleId) {
            return;
        }

        DB::table('module_children')->insert([
            'module_id'   => $tenantModuleId,
            'description' => $source->description,
            'route_name'  => $source->route_name,
            'permission'  => $source->permission,
            'order'       => $source->order,
            'show'        => 'tenant',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('module_children')
            ->where('route_name', 'ventas.nota_credito.index')
            ->delete();
    }
};
