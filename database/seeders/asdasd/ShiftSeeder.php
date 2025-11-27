<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use App\Models\Shift;
class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Shift::create([
            'time' => 'MAÑANA',
        ]);
        Shift::create([
            'time' => 'TARDE',
        ]);
        Shift::create([
            'time' => 'NOCHE',
        ]);
    }
}
