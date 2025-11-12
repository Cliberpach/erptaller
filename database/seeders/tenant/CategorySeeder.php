<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Category;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Category::create([
            'name' => 'GASEOSAS',
        ]);
        Category::create([
            'name' => 'AGUA',
        ]);
        Category::create([
            'name' => 'SNACKS',
        ]);
        Category::create([
            'name' => 'CERVEZAS',
        ]);

        Category::create([
            'name' => 'CREDITOS',
        ]);

    }
}
