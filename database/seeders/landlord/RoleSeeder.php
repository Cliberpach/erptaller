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
        $role = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'TECNICO']);

        $permissions = Permission::all();
        $role->syncPermissions($permissions);
        $role2->syncPermissions($permissions);
    }
}
