<?php

namespace Database\Seeders\landlord;

use App\Models\Landlord\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Customer::create([
        //     'document_number' => '99999999',
        //     'name' => 'VARIOS',
        //     'phone' => '99999999'
        // ]);

        $customer                               =   new Customer();
        $customer->document_number              =   '99999999';
        $customer->name                         =   'VARIOS';
        $customer->phone                        =   '99999999';
        $customer->type_identity_document_id    =   1;
        $customer->type_document_code           =   '01';
        $customer->type_document_name           =   "DOCUMENTO NACIONAL DE IDENTIDAD";
        $customer->type_document_abbreviation   =   'DNI';

        $customer->save();

    }
}
