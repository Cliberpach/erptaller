<?php

namespace App\Http\Services\Tenant\WorkShop\Colors;

use App\Almacenes\Producto;
use App\Models\Tenant\WorkShop\Color;
use Illuminate\Support\Collection;

class ColorService
{
    public function store(array $datos): Color
    {
        $color              =   new Color();
        $color->description =   mb_strtoupper($datos['description'], 'UTF-8');
        $color->codigo      =   $datos['codigo'] ?? null;
        $color->save();

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
}
