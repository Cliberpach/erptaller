<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name'      =>  'CATEGORIA',
            'status'    =>  'INACTIVE'
        ]);

        Category::create([
            'name' => 'RESPUESTO',
        ]);
    }
}
