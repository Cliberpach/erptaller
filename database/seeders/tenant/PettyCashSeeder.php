<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use App\Models\PettyCash;

class PettyCashSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        PettyCash::create([
            'name' =>'CAJA PRINCIPAL',
        ]);


    }
}
