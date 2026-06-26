<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin   = Role::firstOrCreate(['name' => 'admin']);
        $ventas  = Role::firstOrCreate(['name' => 'ventas']);
        $tecnico = Role::firstOrCreate(['name' => 'tecnico']);

        // admin = todos los permisos. ventas/tecnico quedan sin permisos por ahora
        // (el detalle se define en una etapa aparte; aquí solo deben EXISTIR).
        $admin->syncPermissions(Permission::all());
    }
}
