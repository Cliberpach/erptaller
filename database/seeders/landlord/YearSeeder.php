<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Seeder;
use App\Models\Landlord\Year;

class YearSeeder extends Seeder
{
    public function run(): void
    {
        for ($year = 1950; $year <= 2050; $year++) {
            Year::create([
                'description' => (string)$year
            ]);
        }
    }
}
