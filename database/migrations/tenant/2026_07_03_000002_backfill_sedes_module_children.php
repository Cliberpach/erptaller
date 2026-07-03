<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Sedes" se agregó al catálogo module_children del landlord (id 39, route
 * mantenimientos.sedes.index) DESPUÉS de que varios tenants ya fueran provisionados/clonados
 * del template. TenantModuleSeeder copia en vivo del landlord (correcto para tenants nuevos y
 * para tenant:rebuild-template), pero los tenants YA clonados de un template viejo se quedaron
 * sin esa fila -> el submenú "Sedes" no aparece pese a tener el permiso.
 *
 * IDEMPOTENTE: no inserta si el route_name ya existe en este tenant.
 * GUARDA: si el tenant aún no tiene módulos (BD nueva sin seedear), no hace nada -> lo cubre
 * TenantModuleSeeder normalmente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('module_children')->where('route_name', 'mantenimientos.sedes.index')->exists()) {
            return; // ya la tiene
        }

        $mantenimiento_id = DB::table('modules')->where('description', 'Mantenimiento')->value('id');

        if (! $mantenimiento_id) {
            return; // tenant nuevo: lo siembra TenantModuleSeeder
        }

        $sedes = DB::connection('landlord')->table('module_children')
            ->where('route_name', 'mantenimientos.sedes.index')
            ->first();

        if (! $sedes) {
            return;
        }

        DB::table('module_children')->insert([
            'module_id'   => $mantenimiento_id,
            'description' => $sedes->description,
            'route_name'  => $sedes->route_name,
            'permission'  => $sedes->permission,
            'order'       => $sedes->order,
            'show'        => 'tenant',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('module_children')->where('route_name', 'mantenimientos.sedes.index')->delete();
    }
};
