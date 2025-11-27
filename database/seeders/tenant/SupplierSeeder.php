<?php

namespace Database\Seeders\tenant;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'type_identity_document_id' => 1,
            'type_document_name'        => 'DOCUMENTO NACIONAL DE IDENTIDAD',
            'type_document_abbreviation'=> 'DNI',
            'type_document_code'        => '01',
            'document_number'           => '99999999',
            'name'                      => 'PROVEEDOR VARIOS',
        ]);
    }
}
