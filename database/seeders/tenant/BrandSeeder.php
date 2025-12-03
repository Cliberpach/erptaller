<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Brand::create([
            'name' => 'MARCA',
            'status' => 'INACTIVE'
        ]);

        Brand::create([
            'name' => 'NACIONAL'
        ]);
    }
}
