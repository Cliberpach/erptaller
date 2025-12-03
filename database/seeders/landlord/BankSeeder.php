<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\GeneralTable\GeneralTable;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // CREAR MAESTRO
        // ==============================
        $master = GeneralTable::create([
            'name'              => 'BANCOS',
            'description'       => 'Lista de bancos registrados en el sistema',
            'symbol'            => 'BNK',
            'parameter'         => 'BNK',
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
        // LISTA DE BANCOS DEL PERÚ (20+)
        // ==============================
        $banks = [
            ['name' => 'BANCO DE CRÉDITO DEL PERÚ (BCP)', 'abbr' => 'BCP'],
            ['name' => 'INTERBANK', 'abbr' => 'IBK'],
            ['name' => 'BANCO CONTINENTAL BBVA', 'abbr' => 'BBVA'],
            ['name' => 'SCOTIABANK PERÚ', 'abbr' => 'SCO'],
            ['name' => 'BANCO PICHINCHA', 'abbr' => 'BPI'],
            ['name' => 'BANCO FALABELLA', 'abbr' => 'FAL'],
            ['name' => 'BANCO RIPLEY', 'abbr' => 'RIP'],
            ['name' => 'BANCO GNB PERÚ', 'abbr' => 'GNB'],
            ['name' => 'BANCO DE LA NACIÓN', 'abbr' => 'BNN'],
            ['name' => 'MIBANCO', 'abbr' => 'MIB'],
            ['name' => 'CAJA AREQUIPA', 'abbr' => 'CAQ'],
            ['name' => 'CAJA HUANCAYO', 'abbr' => 'CHU'],
            ['name' => 'CAJA PIURA', 'abbr' => 'CPI'],
            ['name' => 'CAJA CUSCO', 'abbr' => 'CCU'],
            ['name' => 'CAJA TRUJILLO', 'abbr' => 'CTR'],
            ['name' => 'CAJA TACNA', 'abbr' => 'CTA'],
            ['name' => 'BANCO SANTANDER PERÚ', 'abbr' => 'SAN'],
            ['name' => 'CITIBANK PERÚ', 'abbr' => 'CIT'],
            ['name' => 'HSBC PERÚ', 'abbr' => 'HSBC'],
            ['name' => 'BANBIF (BANCO INTERAMERICANO DE FINANZAS)', 'abbr' => 'BNF'],
            ['name' => 'CMAC MAYNAS', 'abbr' => 'CMY'],
            ['name' => 'CAJA METROPOLITANA', 'abbr' => 'CME'],
            ['name' => 'COOPAC ABACO', 'abbr' => 'CAB'],
        ];


        // ==============================
        // REGISTRAR DETALLES
        // ==============================
        foreach ($banks as $bank) {
            GeneralTableDetail::create([
                'general_table_id' => $master->id,
                'category_id' => null, 
                'name' => $bank['name'],
                'description' => 'Banco del Perú',
                'symbol' => $bank['abbr'],
                'parameter' => $bank['abbr'],
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
