<?php

namespace Database\Seeders\tenant;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = database_path("data/districts.json");
        $json = file_get_contents($file);
        $districts = json_decode($json);
        foreach ($districts as $district) {
            District::create([
                'id' => $district->id,
                'department_id' => $district->department_id,
                'department' => $district->department,
                'province_id' => $district->province_id,
                'province' => $district->province,
                'name' => $district->name,
                'legal_name' => $district->legal_name
            ]);
        }
    }
}
