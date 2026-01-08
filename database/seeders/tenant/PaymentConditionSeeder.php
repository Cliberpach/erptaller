<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;

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
        $item->editable             =   false;
        $item->save();

        $item                       =   new PaymentCondition();
        $item->name                 =   'CREDITO';
        $item->type                 =   'CREDITO';
        $item->nro_days             =   10;
        $item->editable             =   true;
        $item->save();

        $item                       =   new PaymentCondition();
        $item->name                 =   'CREDITO';
        $item->type                 =   'CREDITO';
        $item->nro_days             =   20;
        $item->editable             =   true;
        $item->save();
    }
}
