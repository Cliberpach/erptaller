<?php

namespace Database\Seeders\Landlord;

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
        // Caja
        $petty_cash = Module::create([
            'description' => 'Cajas',
            'order' => '1'
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
            'route_name' => 'cajas.apertura_cierre',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $petty_cash->id,
            'description' => 'Egresos',
            'route_name' => 'cajas.egreso',
            'order' => '2'
        ]);


        // Taller
        $taller = Module::create([
            'description' => 'Taller',
            'order' => '1'
        ]);

        ModuleChild::create([
            'module_id'     => $taller->id,
            'description'   => 'Vehiculos',
            'route_name'    => 'taller.vehiculos',
            'order'         => '2'
        ]);


        // Ventas
        $sale = Module::create([
            'description' => 'Ventas',
            'order' => '1'
        ]);

        ModuleChild::create([
            'module_id'     => $sale->id,
            'description'   => 'Comprobante Venta',
            'route_name'    => 'ventas.comprobante_venta',
            'order'         => '2'
        ]);


        // ModuleChild::create([
        //     'module_id' => $sale->id,
        //     'description' => 'Comprobante Electrónico',
        //     'route_name' => 'ventas.comprobante_electronico',
        //     'order' => '2'

        // // ModuleChild::create([
        // //     'module_id' => $sale->id,
        // //     'description' => 'Cotización',
        // //     'route_name' => 'ventas.cotizacion',
        // //     'order' => '2'
        // // ]);

        ModuleChild::create([
            'module_id' => $sale->id,
            'description' => 'Clientes',
            'route_name' => 'ventas.cliente',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $sale->id,
            'description'   => 'Métodos Pago',
            'route_name'    => 'ventas.metodo_pago',
            'order'         => '2'
        ]);

        // Inventario
        $inventory = Module::create([
            'description' => 'Inventario',
            'order' => '1'
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

        // ModuleChild::create([
        //     'module_id' => $inventory->id,
        //     'description' => 'Servicio',
        //     'route_name' => 'inventarios.servicio',
        //     'order' => '2'
        // ]);

        // ModuleChild::create([
        //     'module_id' => $inventory->id,
        //     'description' => 'Movimiento',
        //     'route_name' => 'inventarios.movimiento',
        //     'order' => '2'
        // ]);

        // // ModuleChild::create([
        // //     'module_id' => $inventory->id,
        // //     'description' => 'Devolución Proveedor',
        // //     'route_name' => 'inventarios.devolucion_proveedor',
        // //     'order' => '2'
        // // ]);

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

        // Compras
        $purchase = Module::create([
            'description' => 'Compras',
            'order' => '1'
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


        // Reportes
        $report = Module::create([
            'description'   => 'Reportes',
            'order'         => '1'
        ]);

        ModuleChild::create([
            'module_id'     => $report->id,
            'description'   => 'Reporte de Venta',
            'route_name'    => 'reportes.reporte_venta',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $report->id,
            'description'   => 'Reporte de Campos',
            'route_name'    => 'reportes.reporte_campo',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $report->id,
            'description'   => 'Reporte Contable',
            'route_name'    => 'reportes.reporte_contable',
            'order'         => '2'
        ]);

        ModuleChild::create([
            'module_id'     => $report->id,
            'description'   => 'Comprobantes Reserva',
            'route_name'    => 'reportes.comprobantes_reservas',
            'order'         => '2'
        ]);


        // Mantenimiento
        $maintenance = Module::create([
            'description' => 'Mantenimiento',
            'order' => '1',
            'show' => 'landlord'
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
            'description' => 'Planes',
            'route_name' => 'mantenimientos.plan',
            'order' => '2',
            'show' => 'landlord'
        ]);

        ModuleChild::create([
            'module_id' => $maintenance->id,
            'description' => 'Usuarios',
            'route_name' => 'mantenimientos.usuario',
            'order' => '2',
            'show' => 'tenant'
        ]);

        ModuleChild::create([
            'module_id' => $maintenance->id,
            'description' => 'Roles',
            'route_name' => 'mantenimientos.rol',
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

        // Consultas
        $consultas = Module::create([
            'description' => 'Consultas',
            'order' => '1'
        ]);

        ModuleChild::create([
            'module_id' => $consultas->id,
            'description' => 'Creditos',
            'route_name' => 'consultas.creditos',
            'order' => '2'
        ]);

        ModuleChild::create([
            'module_id' => $consultas->id,
            'description' => 'Reservas',
            'route_name' => 'consultas.reservas',
            'order' => '3'
        ]);

    }
}
