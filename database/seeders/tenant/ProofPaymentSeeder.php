<?php

namespace Database\Seeders\tenant;

use App\Models\ProofPayment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProofPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProofPayment::create([
            'description' => 'BOLETA ELECTRÓNICA'
        ]);

        ProofPayment::create([
            'description' => 'FACTURA ELECTRÓNICA'
        ]);

        ProofPayment::create([
            'description' => 'PLANILLA'
        ]);

        ProofPayment::create([
            'description' => 'MENÚ'
        ]);

        ProofPayment::create([
            'description' => 'OTROS'
        ]);
    }
}
