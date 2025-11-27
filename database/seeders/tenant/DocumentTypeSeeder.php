<?php

namespace Database\Seeders\tenant;

use App\Models\DocumentType;
use App\Models\Tenant\DocumentSerialization;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentType::create([
            'id'            => "01",
            'status'        => 1,
            'abbreviation'  => 'FT',
            'description'   => 'FACTURA ELECTRÓNICA',
            'code'          => '01',
            'prefix_serie'  => 'F'
        ]);

        DocumentType::create([
            'id'            => "03",
            'status'        => 1,
            'abbreviation'  => 'BV',
            'description'   => 'BOLETA DE VENTA ELECTRÓNICA',
            'code'          => '03',
            'prefix_serie'  => 'B'
        ]);

        DocumentType::create([
            'id'            => "06",
            'status'        =>  1,
            'abbreviation'  => 'NCB',
            'description'   => 'NOTA DE CRÉDITO BOLETA',
            'code'          =>  '07',
            'prefix_serie'  => 'BB'
        ]);

        DocumentType::create([
            'id'            => "07",
            'status'        => 1,
            'abbreviation'  => 'NCF',
            'description'   => 'NOTA DE CRÉDITO FACTURA',
            'code'          =>  '07',
            'prefix_serie'  => 'FF'
        ]);

        DocumentType::create([
            'id'            => "08",
            'status'        => 1,
            'abbreviation'  => 'ND',
            'description'   => 'NOTA DE DÉBITO',
            'code'          => '08',
            'prefix_serie'  => 'ND'
        ]);


        DocumentType::create([
            'id'            => "09",
            'status'        => 1,
            'abbreviation'  => 'GRE',
            'description'   => 'GUIA DE REMISIÓN REMITENTE',
            'code'          => '09',
            'prefix_serie'  => 'T'
        ]);


        DocumentType::create([
            'id'            => "20",
            'status'        => 1,
            'abbreviation'  => 'CRE',
            'description'   => 'COMPROBANTE DE RETENCIÓN ELECTRÓNICA',
            'code'          => '20'
        ]);


        DocumentType::create([
            'id'            => "76",
            'status'        => 1,
            'abbreviation'  => 'NE',
            'description'   => 'NOTA DE ENTRADA',
            'code'          => '76'
        ]);

        DocumentType::create([
            'id'            => "80",
            'status'        => 1,
            'abbreviation'  => 'NV',
            'description'   => 'NOTA DE VENTA',
            'code'          => '80',
            'prefix_serie'  => 'NV'
        ]);

        DocumentType::create([
            'id'            => "50",
            'status'        => 1,
            'abbreviation'  => 'T',
            'description'   => 'TICKET',
            'code'          => '50'
        ]);

        DocumentType::create([
            'id'            => "51",
            'status'        => 1,
            'abbreviation'  => 'TC',
            'description'   => 'TICKET DE COMPRA',
            'code'          => '51'
        ]);

        DocumentType::create([
            'id'            => "52",
            'status'        => 1,
            'abbreviation'  => 'NI',
            'description'   => 'NOTA DE INGRESO',
            'code'          => '52'
        ]);

        DocumentType::create([
            'id'            => "53",
            'status'        => 1,
            'abbreviation'  => 'NS',
            'description'   => 'NOTA DE SALIDA',
            'code'          => '53'
        ]);

    }
}
