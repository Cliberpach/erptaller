<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\ModuleChild;
use App\Models\ModuleGrandChild;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //============ DASHBOARD ============
        $dashboard = Module::create([
            'description'   =>  'Dashboard',
            'order'         =>  '1',
            'icon'          =>  'dashboard-graph-analytics-report-svgrepo-com.svg'
        ]);

        ModuleChild::create([
            'module_id' => $dashboard->id,
            'description' => 'Dashboard',
            'route_name' => 'dashboard.dashboard.index',
            'order' => '2'
        ]);

        //========== CAJA =========
        $petty_cash = Module::create([
            'description'   =>  'Cajas',
            'order'         =>  '1',
            'icon'          =>  'cashier_machine_cash_register_pos_icon_225168.svg'
        ]);

        ModuleChild::create([
            'module_id' => $petty_cash->id,
            'description' => 'Caja',
            'route_name' => 'cajas.caja',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $petty_cash->id,
            'description' => 'Apertura/Cierre',
            'route_name' => 'movimientos_caja.apertura_cierre',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $petty_cash->id,
            'description' => 'Egresos',
            'route_name' => 'cajas.egreso',
            'order' => '2'
        ]);


        //=========== TALLER ==========
        $taller = Module::create([
            'description'   =>  'Taller',
            'order'         =>  '1',
            'icon'          =>  'a-car.svg'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Cotizaciones',
            'route_name'    => 'taller.cotizaciones.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Ordenes Trabajo',
            'route_name'    => 'taller.ordenes_trabajo.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Servicios',
            'route_name'    => 'taller.servicios.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Colores',
            'route_name'    => 'taller.colores.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Años',
            'route_name'    => 'taller.years.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Marcas',
            'route_name'    => 'taller.marcas.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Modelos',
            'route_name'    => 'taller.modelos.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Vehiculos',
            'route_name'    => 'taller.vehiculos.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Citas',
            'route_name'    => 'taller.citas.index',
            'order'         => '2'
        ]);

        //========== VENTAS ============
        $sale = Module::create([
            'description'   => 'Ventas',
            'order'         => '1',
            'icon'          => 'percentage-sales-svgrepo-com.svg'
        ]);

        ModuleChild::create([
            'module_id'     => $sale->id,
            'description'   => 'Comprobante Venta',
            'route_name'    => 'ventas.comprobante_venta',
            'order'         => '2'
        ]);


        ModuleChild::create([
            'module_id' => $sale->id,
            'description' => 'Clientes',
            'route_name' => 'ventas.cliente',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $sale->id,
            'description' => 'Notas de Crédito',
            'route_name' => 'ventas.nota_credito.index',
            'order' => '3'
        ]);

        // Métodos Pago + Condiciones Pago se movieron a Mantenimiento (ver $maintenance).

        /*============ CUENTAS ==========*/
        $accounts = Module::create([
            'description'   =>  'Cuentas',
            'order'         =>  '1',
            'icon'          =>  'credit-card-svgrepo-com.svg'
        ]);

        ModuleChild::create([
            'module_id'     => $accounts->id,
            'description'   => 'Cuentas Cliente',
            'route_name'    => 'cuentas.cliente.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $accounts->id,
            'description'   => 'Cuentas Proveedor',
            'route_name'    => 'cuentas.proveedor.index',
            'order'         => '2'
        ]);

        //=========== INVENTARIO =========
        $inventory = Module::create([
            'description'   =>  'Inventario',
            'order'         =>  '1',
            'icon'          =>  'warehouse-svgrepo-com.svg'
        ]);

        $product = ModuleChild::create([
            'module_id' => $inventory->id,
            'description' => 'Productos',
            'order' => '2'
        ]);

        ModuleGrandchild::create([
            'module_child_id' => $product->id,
            'description' => 'Categorías',
            'route_name' => 'inventarios.productos.categoria',
            'order' => '3'
        ]);

        ModuleGrandchild::create([
            'module_child_id' => $product->id,
            'description' => 'Marca',
            'route_name' => 'inventarios.productos.marca',
            'order' => '3'
        ]);

        ModuleGrandchild::create([
            'module_child_id' => $product->id,
            'description' => 'Producto',
            'route_name' => 'inventarios.productos.producto',
            'order' => '3'
        ]);

        ModuleChild::create([
            'module_id'  => $inventory->id,
            'description' => 'Almacenes',
            'route_name' => 'inventarios.almacenes.index',
            'order'      => '2',
            'show'       => 'tenant',
        ]);

        ModuleChild::create([
            'module_id' => $inventory->id,
            'description' => 'Kardex',
            'route_name' => 'inventory.kardex.index',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $inventory->id,
            'description' => 'Inventario',
            'route_name' => 'inventarios.inventario',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $inventory->id,
            'description' => 'Kardex Valorizado',
            'route_name' => 'inventarios.kardex_valorizado',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $inventory->id,
            'description'   => 'Nota Ingreso',
            'route_name'    => 'inventarios.nota_ingreso',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $inventory->id,
            'description'   => 'Nota Salida',
            'route_name'    => 'inventarios.nota_salida',
            'order'         => '2'
        ]);

        //============= COMPRAS =============
        $purchase = Module::create([
            'description'   =>  'Compras',
            'order'         =>  '1',
            'icon'          =>  'shopping-cart-svgrepo-com.svg'
        ]);

        ModuleChild::create([
            'module_id' => $purchase->id,
            'description' => 'Proveedores',
            'route_name' => 'compras.proveedor',
            'order' => '2'
        ]);



        ModuleChild::create([
            'module_id'     => $purchase->id,
            'description'   => 'Documento de Compra',
            'route_name'    => 'compras.documento_compra.index',
            'order'         => '2'
        ]);


        //=========== REPORTES =========
        $report = Module::create([
            'description'   =>  'Reportes',
            'order'         =>  '1',
            'icon'          =>  'analytics-financial-svgrepo-com.svg'
        ]);

        ModuleChild::create([
            'module_id'     => $report->id,
            'description'   => 'Reporte de Venta',
            'route_name'    => 'reportes.reporte_venta',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $report->id,
            'description'   => 'Reporte Contable',
            'route_name'    => 'reportes.reporte_contable',
            'order'         => '2'
        ]);


        //========= MANTENIMIENTO ===========
        $maintenance = Module::create([
            'description'   =>  'Mantenimiento',
            'order'         =>  '1',
            'show'          =>  'landlord',
            'icon'          =>  'configuration-svgrepo-com.svg'
        ]);

        ModuleChild::create([
            'module_id' => $maintenance->id,
            'description' => 'Empresa',
            'route_name' => 'mantenimientos.empresa',
            'order' => '2',
            'show' => 'landlord'
        ]);

        ModuleChild::create([
            'module_id' => $maintenance->id,
            'description' => 'Cuentas',
            'route_name' => 'mantenimientos.cuentas.index',
            'order' => '2',
            'show' => 'tenant'
        ]);

        ModuleChild::create([
            'module_id' => $maintenance->id,
            'description' => 'Planes',
            'route_name' => 'mantenimientos.plan',
            'order' => '2',
            'show' => 'landlord'
        ]);


        ModuleChild::create([
            'module_id' => $maintenance->id,
            'description' => 'Cargos',
            'route_name' => 'mantenimientos.cargos.index',
            'order' => '2',
            'show' => 'tenant'
        ]);

        ModuleChild::create([
            'module_id' => $maintenance->id,
            'description' => 'Colaboradores',
            'route_name' => 'mantenimientos.colaboradores.index',
            'order' => '2',
            'show' => 'tenant'
        ]);

        ModuleChild::create([
            'module_id' => $maintenance->id,
            'description' => 'Horario de atención',
            'route_name' => 'mantenimientos.horario',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $maintenance->id,
            'description'   => 'Configuración',
            'route_name'    => 'mantenimientos.configuracion',
            'order'         => '2'
        ]);

        // Movidos desde Ventas: ahora son config de pago de mantenimiento.
        ModuleChild::create([
            'module_id'     => $maintenance->id,
            'description'   => 'Métodos Pago',
            'route_name'    => 'ventas.metodo_pago',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $maintenance->id,
            'description'   => 'Condiciones Pago',
            'route_name'    => 'ventas.condiciones_pago.index',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'  => $maintenance->id,
            'description'=> 'Sedes',
            'route_name' => 'mantenimientos.sedes.index',
            'order'      => '2',
            'show'       => 'tenant',
        ]);

        //========== SEGURIDAD ===========
        // Multi-Sede: Roles y Usuarios se mueven aquí desde Mantenimiento. show='tenant'
        // (módulo + hijos son tenant-only; al provisionar la BD del tenant los copia con
        // show por defecto 'tenant'). Posición: justo después de Mantenimiento (por id).
        $seguridad = Module::create([
            'description' => 'Seguridad',
            'order'       => '1',
            'show'        => 'tenant',
            // Aún sin SVG propio: el archivo no existe → el menú usa el ícono fallback.
            // Soltá public/assets/menu-icons/security.svg para darle ícono propio.
            'icon'        => 'security.svg',
        ]);

        ModuleChild::create([
            'module_id'  => $seguridad->id,
            'description' => 'Usuarios',
            'route_name' => 'mantenimientos.usuario.index',
            'order'      => '2',
            'show'       => 'tenant',
        ]);

        ModuleChild::create([
            'module_id'  => $seguridad->id,
            'description' => 'Roles',
            'route_name' => 'mantenimientos.rol',
            'order'      => '2',
            'show'       => 'tenant',
        ]);

        //========== CONSULTAS ===========
        $consultas = Module::create([
            'description'   =>  'Consultas',
            'order'         =>  '1',
            'icon'          =>  'analytics-report-svgrepo-com.svg'
        ]);

        ModuleChild::create([
            'module_id' => $consultas->id,
            'description' => 'Creditos',
            'route_name' => 'consultas.creditos',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $consultas->id,
            'description' => 'Vehículos',
            'route_name' => 'consultas.vehiculos.index',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $consultas->id,
            'description' => 'Alertas',
            'route_name' => 'consultas.notificaciones.index',
            'order' => '2'
        ]);

        //========== KARDEX (Paso 4) ===========
        // Hoy solo "Kardex Cuentas" es funcional; los otros 4 son "próximamente"
        // (route_name NULL -> el nav los renderiza deshabilitados, sin ruta muerta).
        $kardex = Module::create([
            'description' => 'Kardex',
            'order'       => '1',
            'icon'        => 'analytics-report-svgrepo-com.svg',
        ]);

        ModuleChild::create([
            'module_id'  => $kardex->id,
            'description' => 'Kardex Cuentas',
            'route_name' => 'kardex.cuentas.index',
            'order'      => '2',
        ]);

        foreach (['Kardex Clientes', 'Kardex Unidades', 'Kardex Productos', 'Kardex Servicios'] as $proximamente) {
            ModuleChild::create([
                'module_id'  => $kardex->id,
                'description' => $proximamente,
                'route_name' => null, // próximamente
                'order'      => '2',
            ]);
        }

        // Permiso compartido a los 5 hijos (roadmap visible para quien tiene acceso al Kardex).
        ModuleChild::where('module_id', $kardex->id)->update(['permission' => 'kardex.cuentas.ver']);

        // ===== Permisos P1: mapeo nodo del menú → permiso Spatie (data-driven) =====
        // Se puebla la columna `permission` por route_name. Nodos sin entrada (ej.
        // mantenimientos.plan landlord-only, o el contenedor "Productos" con route vacía)
        // quedan en NULL → el menú (P2) los resuelve. El provisioning copia esta columna.
        $mapaPermisos = [
            'dashboard.dashboard.index'           => 'dashboard.ver',
            'cajas.caja'                          => 'cajas.ver',
            'movimientos_caja.apertura_cierre'    => 'cajas.abrir_cerrar',
            'cajas.egreso'                        => 'cajas.egresos',
            'taller.cotizaciones.index'           => 'taller.cotizaciones.ver',
            'taller.ordenes_trabajo.index'        => 'taller.ot.ver',
            'taller.servicios.index'              => 'taller.servicios.gestionar',
            'taller.colores.index'                => 'taller.maestros.gestionar',
            'taller.years.index'                  => 'taller.maestros.gestionar',
            'taller.marcas.index'                 => 'taller.maestros.gestionar',
            'taller.modelos.index'                => 'taller.maestros.gestionar',
            'taller.vehiculos.index'              => 'taller.vehiculos.gestionar',
            'taller.citas.index'                  => 'taller.citas.gestionar',
            'ventas.comprobante_venta'            => 'ventas.ver',
            'ventas.cliente'                      => 'ventas.clientes.gestionar',
            'ventas.nota_credito.index'           => 'ventas.ver',
            // Movidos a Mantenimiento: ahora gestionados con el permiso de mantenimiento.
            'ventas.metodo_pago'                  => 'mantenimiento.config_pago.gestionar',
            'ventas.condiciones_pago.index'       => 'mantenimiento.config_pago.gestionar',
            'cuentas.cliente.index'               => 'cuentas.cxc.ver',
            'cuentas.proveedor.index'             => 'cuentas.cxp.ver',
            'inventarios.productos.categoria'     => 'inventario.productos.gestionar',
            'inventarios.productos.marca'         => 'inventario.productos.gestionar',
            'inventarios.productos.producto'      => 'inventario.productos.gestionar',
            'inventarios.almacenes.index'         => 'inventario.almacenes.gestionar',
            'inventory.kardex.index'              => 'inventario.kardex.ver',
            'inventarios.inventario'              => 'inventario.ver',
            'inventarios.kardex_valorizado'       => 'inventario.ver_costos',
            'inventarios.nota_ingreso'            => 'inventario.notas.gestionar',
            'inventarios.nota_salida'             => 'inventario.notas.gestionar',
            'compras.proveedor'                   => 'compras.ver',
            'compras.documento_compra.index'      => 'compras.ver',
            'reportes.reporte_venta'              => 'reportes.ventas',
            'reportes.reporte_contable'           => 'reportes.contable',
            'mantenimientos.empresa'              => 'mantenimiento.empresa.gestionar',
            'mantenimientos.cuentas.index'        => 'mantenimiento.cuentas_bancarias.gestionar',
            // 'mantenimientos.plan'              => NULL (landlord-only, no se mapea)
            'mantenimientos.cargos.index'         => 'mantenimiento.cargos.gestionar',
            'mantenimientos.colaboradores.index'  => 'mantenimiento.colaboradores.gestionar',
            'mantenimientos.horario'              => 'mantenimiento.horario.gestionar',
            'mantenimientos.configuracion'        => 'mantenimiento.configuracion.gestionar',
            'mantenimientos.sedes.index'          => 'mantenimiento.sedes.gestionar',
            'mantenimientos.usuario.index'        => 'mantenimiento.usuarios.gestionar',
            'mantenimientos.rol'                  => 'mantenimiento.roles.gestionar',
            'consultas.creditos'                  => 'consultas.ver',
            'consultas.vehiculos.index'           => 'consultas.ver',
            'consultas.notificaciones.index'      => 'consultas.ver',
        ];

        foreach ($mapaPermisos as $route => $perm) {
            ModuleChild::where('route_name', $route)->update(['permission' => $perm]);
            ModuleGrandChild::where('route_name', $route)->update(['permission' => $perm]);
        }
    }
}
