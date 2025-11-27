<?php

namespace Database\Seeders\landlord;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'description' => 'PLAN BÁSICO',
            'number_fields' => 3,
            'price' => 100,
        ]);

        Plan::create([
            'description' => 'PLAN PREMIUN',
            'number_fields' => 6,
            'price' => 200,
        ]);

        Plan::create([
            'description' => 'PLAN FULL',
            'number_fields' => 9999,
            'price' => 300,
        ]);
    }
}
