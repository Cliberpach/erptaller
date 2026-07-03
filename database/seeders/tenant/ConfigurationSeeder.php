<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;

use App\Models\Tenant\Configuration;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configuration              =   new Configuration();
        $configuration->description =   'MOSTRAR CUENTAS BANCARIAS';
        $configuration->property    =   '1';
        $configuration->symbol      =   'MCB';
        $configuration->group_name  =   'EMPRESA';
        $configuration->save();

        $configuration              =   new Configuration();
        $configuration->description =   'VALIDAR STOCK EN ORDENES DE TRABAJO';
        $configuration->property    =   '0';
        $configuration->symbol      =   'VSOT';
        $configuration->group_name  =   'OT';
        $configuration->save();

        // VEP: '1' = ventas puede editar el precio de venta (default -> preserva el comportamiento
        // actual). firstOrCreate -> idempotente (re-sembrar no duplica).
        Configuration::firstOrCreate(
            ['symbol' => 'VEP'],
            [
                'description' => 'EL USUARIO VENTAS PUEDE EDITAR EL PRECIO DE VENTA',
                'property'    => '1',
                'group_name'  => 'VENTAS',
            ]
        );

        // VVC: '1' = ventas puede ver el costo del producto (default -> preserva el comportamiento
        // actual). Solo el flag; el enforcement (ocultar costo) es una tarea aparte por capas.
        Configuration::firstOrCreate(
            ['symbol' => 'VVC'],
            [
                'description' => 'EL USUARIO VENTAS PUEDE VER EL COSTO DEL PRODUCTO',
                'property'    => '1',
                'group_name'  => 'VENTAS',
            ]
        );

        // AMB_GRE: ambiente de facturación electrónica Greenter. Valores válidos: DEMO |
        // PRODUCCION (InvoicingManager los valida exacto). Default DEMO -> ningún tenant nuevo
        // factura en producción sin que alguien lo cambie explícitamente.
        Configuration::firstOrCreate(
            ['symbol' => 'AMB_GRE'],
            [
                'description' => 'AMBIENTE DE FACTURACIÓN ELECTRÓNICA (GREENTER)',
                'property'    => 'DEMO',
                'group_name'  => 'SUNAT',
            ]
        );

        // UMB_DNI: monto mínimo (S/) a partir del cual una boleta exige DNI del cliente.
        Configuration::firstOrCreate(
            ['symbol' => 'UMB_DNI'],
            [
                'description' => 'MONTO MÍNIMO (S/) PARA EXIGIR DNI EN BOLETA',
                'property'    => '700',
                'group_name'  => 'SUNAT',
            ]
        );
    }
}
