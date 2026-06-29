<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Set de permisos modulo.accion (agrupable por prefijo en la UI).
     * Idempotente (firstOrCreate). Reemplaza el set legacy (estaba dormido, sin uso).
     */
    public function run(): void
    {
        $permisos = [
            // DASHBOARD
            'dashboard.ver',

            // CAJAS
            'cajas.ver', 'cajas.abrir_cerrar', 'cajas.egresos',

            // TALLER - Órdenes de trabajo
            'taller.ot.ver', 'taller.ot.crear', 'taller.ot.editar', 'taller.ot.anular',
            'taller.ot.facturar', 'taller.ot.ver_precios',
            // TALLER - Cotizaciones
            'taller.cotizaciones.ver', 'taller.cotizaciones.crear', 'taller.cotizaciones.editar',
            // TALLER - Maestros
            'taller.servicios.gestionar', 'taller.vehiculos.gestionar',
            'taller.citas.gestionar', 'taller.maestros.gestionar',

            // VENTAS
            'ventas.ver', 'ventas.crear', 'ventas.anular', 'ventas.enviar_sunat', 'ventas.ver_precios',
            'ventas.clientes.gestionar',
            // DEPRECADO: Métodos/Condiciones Pago se movieron a Mantenimiento
            // (mantenimiento.config_pago.gestionar). Ya no protege ninguna ruta/nodo.
            'ventas.config.gestionar',

            // RESERVAS / CAMPOS
            'reservas.ver', 'reservas.crear', 'reservas.editar', 'reservas.anular',

            // INVENTARIO
            'inventario.ver', 'inventario.productos.gestionar', 'inventario.kardex.ver',
            'inventario.notas.gestionar', 'inventario.ver_costos', 'inventario.almacenes.gestionar',

            // COMPRAS
            'compras.ver', 'compras.crear',

            // CUENTAS
            'cuentas.cxc.ver', 'cuentas.cxc.cobrar', 'cuentas.cxp.ver', 'cuentas.cxp.pagar',

            // REPORTES
            'reportes.ventas', 'reportes.contable', 'reportes.inventario',

            // CONSULTAS
            'consultas.ver',

            // KARDEX DE CUENTA (Paso 4)
            'kardex.cuentas.ver',

            // MANTENIMIENTO
            'mantenimiento.usuarios.gestionar', 'mantenimiento.roles.gestionar',
            'mantenimiento.sedes.gestionar', 'mantenimiento.cargos.gestionar',
            'mantenimiento.colaboradores.gestionar', 'mantenimiento.empresa.gestionar',
            'mantenimiento.configuracion.gestionar',
            'mantenimiento.cuentas_bancarias.gestionar', 'mantenimiento.horario.gestionar',
            'mantenimiento.config_pago.gestionar',
        ];

        foreach ($permisos as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}
