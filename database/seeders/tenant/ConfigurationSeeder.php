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
    }
}
