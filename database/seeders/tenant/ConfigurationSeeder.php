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
        $configuration->save();

        $configuration              =   new Configuration();
        $configuration->description =   'VALIDAR STOCK EN ORDENES DE TRABAJO';
        $configuration->property    =   '0';
        $configuration->symbol      =   'VSOT';
        $configuration->save();
    }
}
