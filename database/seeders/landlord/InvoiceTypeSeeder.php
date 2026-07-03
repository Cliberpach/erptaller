<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\GeneralTable\GeneralTable;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use Illuminate\Database\Seeder;

class InvoiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // CREAR MAESTRO
        // ==============================
        $master = GeneralTable::create([
            'name'              => 'COMPROBANTES DE VENTA',
            'description'       => 'Tipos de comprobantes de venta',
            'symbol'            => 'CDV',
            'parameter'         => 'CDV',
            'status'            => 'ACTIVO',
            'editable'          => true,
            'creator_user_id'   => null,
            'editor_user_id'    => null,
            'delete_user_id'    => null,
            'delete_user_name'  => null,
            'editor_user_name'  => null,
            'create_user_name'  => null,
        ]);

        $tipo_doc                   =   new GeneralTableDetail();
        $tipo_doc->general_table_id =   $master->id;
        $tipo_doc->name             =   'BOLETA ELECTRÓNICA';
        $tipo_doc->description      =   'BOLETA ELECTRÓNICA';
        $tipo_doc->symbol           =   '03';
        $tipo_doc->parameter        =   'B';
        $tipo_doc->editable         =   false;
        $tipo_doc->save();

        $tipo_doc                   =   new GeneralTableDetail();
        $tipo_doc->general_table_id =   $master->id;
        $tipo_doc->name             =   'FACTURA ELECTRÓNICA';
        $tipo_doc->description      =   'FACTURA ELECTRÓNICA';
        $tipo_doc->symbol           =   '01';
        $tipo_doc->parameter        =   'F';
        $tipo_doc->editable         =   false;
        $tipo_doc->save();

        $tipo_doc                   =   new GeneralTableDetail();
        $tipo_doc->general_table_id =   $master->id;
        $tipo_doc->name             =   'NOTA DE VENTA';
        $tipo_doc->description      =   'NOTA DE VENTA';
        $tipo_doc->symbol           =   'NV';
        $tipo_doc->parameter        =   'NV';
        $tipo_doc->editable         =   false;
        $tipo_doc->save();

        // Multi-Sede Etapa 5: completar los tipos canónicos de comprobante.
        // symbol = código SUNAT (07/08/09) / interno (50); parameter = letra de serie.
        // NC: la serie dobla la letra del comprobante afectado (Boleta B -> BB, Factura F -> FF).
        $extra = [
            ['name' => 'NOTA DE CRÉDITO ELECTRÓNICA - BOLETA',  'symbol' => '07', 'parameter' => 'BB'],
            ['name' => 'NOTA DE CRÉDITO ELECTRÓNICA - FACTURA', 'symbol' => '07', 'parameter' => 'FF'],
            ['name' => 'NOTA DE DÉBITO ELECTRÓNICA',            'symbol' => '08', 'parameter' => 'FD'],
            ['name' => 'GUÍA DE REMISIÓN',                       'symbol' => '09', 'parameter' => 'T'],
            ['name' => 'TICKET',                                  'symbol' => '50', 'parameter' => 'TV'],
        ];

        foreach ($extra as $e) {
            $tipo_doc                   =   new GeneralTableDetail();
            $tipo_doc->general_table_id =   $master->id;
            $tipo_doc->name             =   $e['name'];
            $tipo_doc->description      =   $e['name'];
            $tipo_doc->symbol           =   $e['symbol'];
            $tipo_doc->parameter        =   $e['parameter'];
            $tipo_doc->editable         =   false;
            $tipo_doc->save();
        }
    }
}
