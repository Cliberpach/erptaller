<?php

namespace App\Http\Services\Landlord\WorkShop\Colors;

use App\Models\Landlord\Color;

class ColorService
{
    private ColorRepository $s_repository;

    public function __construct()
    {
        $this->s_repository =   new ColorRepository();
    }

    public function store(array $datos): Color
    {
        $color =    $this->s_repository->insertColor($datos);

        return $color;
    }

    public function getColor(int $id): Color
    {
        return Color::findOrFail($id);
    }

    public function update(int $id, array $datos): Color
    {
        $datos = collect($datos)->mapWithKeys(function ($value, $key) {
            if (str_ends_with($key, '_edit')) {
                $key = str_replace('_edit', '', $key);
            }
            return [$key => $value];
        })->toArray();


        $color                  =   Color::findOrFail($id);
        $color->description     =   mb_strtoupper($datos['description'], 'UTF-8');
        $color->codigo          =   $datos['codigo'];
        $color->update();
        return $color;
    }

    public function destroy(int $id): Color
    {
        $color = Color::findOrFail($id);
        $color->status = 'INACTIVE';
        $color->update();

        return $color;
    }

    public function insertIfNotExists(string $description)
    {
        $color_exists   =   $this->s_repository->findColor($description);
        if (!$color_exists) {
            $data   =   ['description' => $description, 'codigo' => "#FFFFFF"];
            $color  =   $this->store($data);
            return ['color_insert' => true, 'color' => $color];
        }
        return ['color_insert' => false, 'color' => $color_exists];
    }
}
