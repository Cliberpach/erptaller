<?php

namespace Database\Seeders\tenant;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::updateOrCreate(
            ['id' => 9999],
            [
                'category_id'    => 5,
                'brand_id'       => 5,
                'name'           => 'SERVICIO DE CANCHA',
                'description'    => 'SERVICIO DE CANCHA',
                'sale_price'     => 1.00,
                'purchase_price' => 1.00,
                'stock'          => 1000000,
                'stock_min'      => 1,
                'code_factory'   => '11111',
                'code_bar'       => '11111',
                'image'          => null,
                'estado'         => 'ACTIVO',
            ]
        );
    }
}
