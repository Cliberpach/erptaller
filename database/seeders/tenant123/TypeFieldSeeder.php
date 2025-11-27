<?php

namespace Database\Seeders\tenant;

use App\Models\TypeField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $type_field = new TypeField();
        $type_field->description = 'Fútbol';
        $type_field->save();

        $type_field = new TypeField();
        $type_field->description = 'Voleibol';
        $type_field->save();

        $type_field = new TypeField();
        $type_field->description = 'Tenis';
        $type_field->save();
    }
}
