<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\GeneralTable\GeneralTable;
use App\Models\Landlord\GeneralTable\GeneralTableCategory;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use Illuminate\Database\Seeder;

class GeneralTableSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // CREAR MAESTRO
        // ==============================
        $master = GeneralTable::create([
            'name'              => 'INVENTARIO DEL VEHICULO',
            'description'       => 'Observaciones para orden de Trabajo',
            'symbol'            => 'IDV',
            'parameter'         => 'IDV',
            'status'            => 'ACTIVO',
            'editable'          => true,
            'creator_user_id'   => null,
            'editor_user_id'    => null,
            'delete_user_id'    => null,
            'delete_user_name'  => null,
            'editor_user_name'  => null,
            'create_user_name'  => null,
        ]);


        // ==============================
        // CATEGORÍAS DEL INVENTARIO
        // ==============================
        $categories = [
            'EXTERIORES',
            'INTERIORES',
            'ACCESORIOS',
            'COMPONENTES MECANICOS',
        ];

        $categoryIds = [];

        foreach ($categories as $cat) {
            $categoryIds[$cat] = GeneralTableCategory::create([
                'general_table_id' => $master->id,
                'name' => $cat,
                'status' => 'ACTIVO',
                'editable' => 1,
                'creator_user_id' => null,
                'editor_user_id' => null,
                'delete_user_id' => null,
                'delete_user_name' => null,
                'editor_user_name' => null,
                'create_user_name' => null,
            ])->id;
        }


        // ==============================
        // DETALLES POR CATEGORÍA
        // ==============================

        $items = [

            // EXTERIORES
            'EXTERIORES' => [
                'UNIDAD DE LUCES',
                '1/4 LUCES',
                'ANTENA',
                'ESPEJO LATERAL',
                'CRISTALES',
                'EMBLEMA',
                'LLANTAS (4)',
                'TAPON DE RUEDAS (4)',
                'MOLDURAS COMPLETAS',
                'TAPON DE GASOLINA',
                'CARROCERIA SIN GOLPES',
                'BOCINAS DE CLAXON',
                'LIMPIADORES (PLUMAS)',
            ],

            // INTERIORES
            'INTERIORES' => [
                'INSTRUMENTOS DE TABLERO',
                'CALEFACCION',
                'RADIO/TIPO',
                'BOCINAS',
                'ENCENDEDOR',
                'ESPEJO RETROVISOR',
                'CENICEROS',
                'CINTURONES',
                'BOTONES DE INTERIORES',
                'MANIJAS DE INTERIORES',
                'TAPETES',
                'VESTIDURAS',
            ],

            // ACCESORIOS
            'ACCESORIOS' => [
                'GATO',
                'MANERAL DE GATO',
                'LLAVE DE RUEDAS',
                'ESTUCHE DE HERRAMIENTAS',
                'TRIANGULO DE SEGURIDAD',
                'LLANTA DE REFACCION',
                'EXTINGUIDOR',
            ],

            // COMPONENTES MECANICOS
            'COMPONENTES MECANICOS' => [
                'CLAXON',
                'TAPON DE ACEITE',
                'TAPON DE RADIADOR',
                'VARILLA DE ACEITE',
                'FILTRO DE AIRE',
                'BATERIA (MCA)',
            ],
        ];


        // ==============================
        // CREAR DETALLES
        // ==============================
        foreach ($items as $categoryName => $details) {

            foreach ($details as $name) {

                $abbr = strtoupper(substr(
                    preg_replace('/[^A-Z]/i', '', $name),
                    0,
                    4
                ));

                GeneralTableDetail::create([
                    'general_table_id' => $master->id,
                    'category_id' => $categoryIds[$categoryName],
                    'name' => $name,
                    'description' => $categoryName,
                    'symbol' => $abbr,
                    'parameter' => $abbr,
                    'status' => 'ACTIVO',
                    'editable' => 1,
                    'creator_user_id' => null,
                    'editor_user_id' => null,
                    'delete_user_id' => null,
                    'delete_user_name' => null,
                    'editor_user_name' => null,
                    'create_user_name' => null,
                ]);
            }
        }
    }
}
