<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use App\Models\Bank;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bank::create([
            'description' => 'BANCO DE CRÉDITO DEL PERÚ',
            'abbreviation' => 'BCP',
        ]);

        Bank::create([
            'description' => 'INTERBANK',
            'abbreviation' => 'INTERBANK',
        ]);

        Bank::create([
            'description' => 'BBVA',
            'abbreviation' => 'BBVA',
        ]);
    }
}
