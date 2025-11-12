<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Brand::create([
            'name' => 'COCA COLA',
        ]);
        Brand::create([
            'name' => 'SAN MATEO',
        ]);
        Brand::create([
            'name' => 'LAYS',
        ]);
        Brand::create([
            'name' => 'SUBLIME',
        ]);
        Brand::create([
            'name' => 'PILSEN CALLAO',
        ]);
        Brand::create([
            'name' => 'PILSEN TRUJILLO',
        ]);
        Brand::create([
            'name' => 'CREDITOS',
        ]);
    }
}
