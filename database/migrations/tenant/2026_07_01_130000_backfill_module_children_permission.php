<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill del mapeo nodo->permiso del menú.
 *
 * El TenantModuleSeeder copiaba module_children del central SIN la columna `permission` -> los
 * tenants quedaban con permission NULL en todos los nodos y el nav.blade (que filtra por ese
 * permiso) mostraba el sidebar VACÍO a todo no-admin. Ademas los grandchildren no se copiaban.
 *
 * Este backfill sincroniza desde el CENTRAL (matchean por id -> el seeder copia con el mismo id):
 *   - module_children.permission  (UPDATE)
 *   - module_grand_children faltantes (INSERT con permission)
 *
 * IDEMPOTENTE: re-setea el mismo valor / updateOrInsert por id (no duplica).
 *
 * GUARDA de ordering: en un tenant NUEVO las migraciones corren ANTES del seeder, cuando
 * module_children aún está vacío -> se saltea y lo puebla el TenantModuleSeeder (ya corregido).
 * Sin la guarda, insertaria grandchildren que luego el seeder re-insertaria (choque de PK).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tenant nuevo (aún sin sembrar el menú): lo hace el seeder corregido, no este backfill.
        if (DB::table('module_children')->count() === 0) {
            return;
        }

        // 1. Children: sincronizar permission desde el central (por id).
        $children = DB::connection('landlord')->table('module_children')->get(['id', 'permission']);
        foreach ($children as $c) {
            DB::table('module_children')->where('id', $c->id)->update(['permission' => $c->permission]);
        }

        // 2. Grandchildren: insertar los faltantes desde el central, CON permission (idempotente por id).
        $grands = DB::connection('landlord')->table('module_grand_children')->get();
        foreach ($grands as $g) {
            DB::table('module_grand_children')->updateOrInsert(
                ['id' => $g->id],
                [
                    'module_child_id' => $g->module_child_id,
                    'description'     => $g->description,
                    'route_name'      => $g->route_name,
                    'permission'      => $g->permission,
                    'order'           => $g->order,
                    'show'            => 'tenant',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        // Revertir el mapeo de children a NULL (estado previo al backfill). Los grandchildren
        // insertados se dejan (borrarlos podria afectar menus ya en uso).
        DB::table('module_children')->update(['permission' => null]);
    }
};
