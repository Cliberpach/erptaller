<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now      = now();
        $children = DB::connection('landlord')->table('module_children')->where('show', 'tenant')->get();
        $grands   = DB::connection('landlord')->table('module_grand_children')->where('show', 'tenant')->get();

        // Un módulo se copia al tenant si ES tenant, O si tiene AL MENOS UN hijo tenant
        // (caso "Mantenimiento": módulo show=landlord pero con hijos mixtos landlord/tenant,
        // compartido entre ambos paneles con distinto contenido -> no se puede filtrar solo
        // por el show del módulo, se rompería el lado tenant).
        $moduleIdsConHijoTenant = $children->pluck('module_id')->unique();
        $modules = DB::connection('landlord')->table('modules')
            ->where(function ($q) use ($moduleIdsConHijoTenant) {
                $q->where('show', 'tenant')
                    ->orWhereIn('id', $moduleIdsConHijoTenant);
            })
            ->get();

        if ($modules->isNotEmpty()) {
            DB::table('modules')->insert(
                $modules->map(fn($m) => [
                    'id'          => $m->id,
                    'description' => $m->description,
                    'order'       => $m->order,
                    'icon'        => $m->icon ?? null,
                    'show'        => 'tenant',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ])->toArray()
            );
        }

        if ($children->isNotEmpty()) {
            DB::table('module_children')->insert(
                $children->map(fn($c) => [
                    'id'          => $c->id,
                    'module_id'   => $c->module_id,
                    'description' => $c->description,
                    'route_name'  => $c->route_name,
                    'permission'  => $c->permission, // mapeo nodo->permiso (antes se omitia -> menu vacio)
                    'order'       => $c->order,
                    'show'        => 'tenant',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ])->toArray()
            );
        }

        if ($grands->isNotEmpty()) {
            DB::table('module_grand_children')->insert(
                $grands->map(fn($g) => [
                    'id'              => $g->id,
                    'module_child_id' => $g->module_child_id,
                    'description'     => $g->description,
                    'route_name'      => $g->route_name,
                    'permission'      => $g->permission, // mapeo nodo->permiso (antes se omitia)
                    'order'           => $g->order,
                    'show'            => 'tenant',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ])->toArray()
            );
        }
    }
}
