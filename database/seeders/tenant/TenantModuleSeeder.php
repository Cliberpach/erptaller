<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now      = now();
        $modules  = DB::connection('landlord')->table('modules')->get();
        $children = DB::connection('landlord')->table('module_children')->get();
        $grands   = DB::connection('landlord')->table('module_grand_children')->get();

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
                    'order'           => $g->order,
                    'show'            => 'tenant',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ])->toArray()
            );
        }
    }
}
