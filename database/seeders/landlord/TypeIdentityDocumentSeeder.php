<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\TypeIdentityDocument;
use Illuminate\Database\Seeder;

class TypeIdentityDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    /*
        TIPOS DE DOCUMENTO IDENTIDAD SEGÚN SUNAT:
        01 - DNI
        04 - CARNET EXTRANJERÍA
        06 - RUC
        07 - PASAPORTE
        11 - PARTIDA NACIMIENTO
        00 - OTROS
    */
    public function run(): void
    {
        $type               =   new TypeIdentityDocument();
        $type->name         =   'DOCUMENTO NACIONAL DE IDENTIDAD';
        $type->abbreviation =   'DNI';
        $type->code         =   '01';
        $type->save();

        $type               =   new TypeIdentityDocument();
        $type->name         =   'CARNET EXTRANJERÍA';
        $type->abbreviation =   'CARNET EXT.';
        $type->code         =   '04';
        $type->save();

        $type               =   new TypeIdentityDocument();
        $type->name         =   'RUC';
        $type->abbreviation =   'RUC';
        $type->code         =   '06';
        $type->save();

        $type               =   new TypeIdentityDocument();
        $type->name         =   'PASAPORTE';
        $type->abbreviation =   'PASAPORTE';
        $type->code         =   '07';
        $type->save();

        $type               =   new TypeIdentityDocument();
        $type->name         =   'PARTIDA NACIMIENTO';
        $type->abbreviation =   'P. NAC';
        $type->code         =   '11';
        $type->save();

        $type               =   new TypeIdentityDocument();
        $type->name         =   'CARNET PERMISO TEMPORAL DE PERMANENCIA';
        $type->abbreviation =   'CPP';
        $type->code         =   '00';
        $type->save();
    }
}
