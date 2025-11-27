<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        PaymentMethod::create([
            'description' => 'EFECTIVO',
        ]);
        PaymentMethod::create([
            'description' => 'YAPE',
        ]);
        PaymentMethod::create([
            'description' => 'PLIN',
        ]);
        PaymentMethod::create([
            'description' => 'BIM',
        ]);

    }
}
