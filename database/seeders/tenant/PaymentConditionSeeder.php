<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\Sale\PaymentCondition\PaymentCondition;

class PaymentConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $item                       =   new PaymentCondition();
        $item->name                 =   'CONTADO';
        $item->type                 =   'CONTADO';
        $item->nro_days             =   0;
        $item->save();

    }
}
