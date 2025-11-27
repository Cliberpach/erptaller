<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\GeneralTable\GeneralTable;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use Illuminate\Database\Seeder;

class IdentityDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipo_doc                   =   new GeneralTable();
        $tipo_doc->name             =   'DOCUMENTOS IDENTIDAD';
        $tipo_doc->description      =   'DOCUMENTOS IDENTIDAD';
        $tipo_doc->symbol           =   'DI';
        $tipo_doc->parameter        =   'DI';
        $tipo_doc->editable         =   false;
        $tipo_doc->save();

        $tipo_doc                   =   new GeneralTableDetail();
        $tipo_doc->general_table_id =   2;
        $tipo_doc->name             =   'DNI';
        $tipo_doc->description      =   'DOCUMENTO NACIONAL DE IDENTIDAD';
        $tipo_doc->symbol           =   'DNI';
        $tipo_doc->parameter        =   '01';
        $tipo_doc->editable         =   false;
        $tipo_doc->save();

        $tipo_doc = new GeneralTableDetail();
        $tipo_doc->general_table_id = 2;
        $tipo_doc->name             = 'RUC';
        $tipo_doc->description      = 'Registro Único de Contribuyentes';
        $tipo_doc->symbol           = 'RUC';
        $tipo_doc->parameter        = '06';
        $tipo_doc->editable         = false;
        $tipo_doc->save();

        $tipo_doc1 = new GeneralTableDetail();
        $tipo_doc1->general_table_id = 2;
        $tipo_doc1->name             = 'CARNET EXTRANJERÍA';
        $tipo_doc1->description      = 'Carnet de Extranjería';
        $tipo_doc1->symbol           = 'CE';
        $tipo_doc1->parameter        = '04';
        $tipo_doc1->editable         = false;
        $tipo_doc1->save();

    }
}
