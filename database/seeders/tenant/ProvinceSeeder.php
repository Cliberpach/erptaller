<?php

namespace Database\Seeders\tenant;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = database_path("data/provinces.json");
        $json = file_get_contents($file);
        $provinces = json_decode($json);
        foreach ($provinces as $province) {
            Province::create([
                'id' => $province->id,
                'name' => $province->name,
                'department_id' => $province->department_id
            ]);
        }
    }
}
