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

        // admin = todos los permisos (+ Gate::before en P2). ventas/tecnico = su set operativo.
        $admin->syncPermissions(Permission::all());

        // VENTAS (mostrador: vende, cobra, cotiza, ve stock). SIN reservas.* (módulo a eliminar).
        $ventas->syncPermissions([
            'dashboard.ver',
            'cajas.ver', 'cajas.abrir_cerrar', 'cajas.egresos',
            'ventas.ver', 'ventas.crear', 'ventas.anular', 'ventas.enviar_sunat',
            'ventas.ver_precios', 'ventas.clientes.gestionar',
            'cuentas.cxc.ver', 'cuentas.cxc.cobrar',
            'taller.cotizaciones.ver', 'taller.cotizaciones.crear', 'taller.cotizaciones.editar',
            'taller.ot.ver', 'taller.ot.facturar',
            'consultas.ver',
            'inventario.ver', 'inventario.kardex.ver',
        ]);

        // TECNICO (taller: OT, vehículos, servicios). SIN taller.ot.ver_precios (no ve precios).
        $tecnico->syncPermissions([
            'dashboard.ver',
            'taller.ot.ver', 'taller.ot.crear', 'taller.ot.editar', 'taller.ot.anular',
            'taller.cotizaciones.ver', 'taller.cotizaciones.crear', 'taller.cotizaciones.editar',
            'taller.servicios.gestionar', 'taller.vehiculos.gestionar',
            'taller.citas.gestionar', 'taller.maestros.gestionar',
            'consultas.ver',
            'inventario.ver', 'inventario.kardex.ver',
        ]);
    }
}
