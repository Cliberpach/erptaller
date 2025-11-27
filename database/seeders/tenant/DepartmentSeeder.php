<?php

namespace Database\Seeders\tenant;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = database_path("data/departments.json");
        $json = file_get_contents($file);
        $deparments = json_decode($json);

        foreach ($deparments as $deparment) {
            Department::create([
                'id' => $deparment->id,
                'name' => $deparment->name,
                'zone' => $deparment->zone,
            ]);
        }
    }
}
