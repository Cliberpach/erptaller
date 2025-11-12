<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouse              = new Warehouse();
        $warehouse->descripcion = 'CENTRAL';
        $warehouse->save();

    }
}
