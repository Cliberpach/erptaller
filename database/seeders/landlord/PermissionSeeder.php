<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'usuarios.listar']);
        Permission::create(['name' => 'usuarios.editar']);
        Permission::create(['name' => 'usuarios.editarMiUsuario']);
        Permission::create(['name' => 'usuarios.eliminar']);
        Permission::create(['name' => 'almacen']);
        Permission::create(['name' => 'kardex_producto']);
        Permission::create(['name' => 'marcas']);
        Permission::create(['name' => 'notas_ingreso']);
        Permission::create(['name' => 'tipos_cliente']);
        Permission::create(['name' => 'documentos_compra']);
        Permission::create(['name' => 'cuentas_proveedor']);
        Permission::create(['name' => 'empresas']);
        Permission::create(['name' => 'usuarios.crear']);
        Permission::create(['name' => 'usuarios.ver']);
        Permission::create(['name' => 'usuarios.verMiUsuario']);
        Permission::create(['name' => 'roles']);
        Permission::create(['name' => 'categorias']);
        Permission::create(['name' => 'consulta_lote_productos']);
        Permission::create(['name' => 'productos']);
        Permission::create(['name' => 'notas_salida']);
        Permission::create(['name' => 'ordenes_compra']);
        Permission::create(['name' => 'proveedores']);
        Permission::create(['name' => 'colaboradores']);
        Permission::create(['name' => 'personas']);
    }
}
