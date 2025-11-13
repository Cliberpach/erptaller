<?php

namespace App\Http\Services\Tenant\WorkShop\Years;

use App\Almacenes\Producto;
use App\Models\Tenant\WorkShop\Brand;
use App\Models\Tenant\WorkShop\Color;
use App\Models\Tenant\WorkShop\ModelV;
use App\Models\Tenant\WorkShop\Year;
use Illuminate\Support\Collection;

class YearService
{
    public function store(array $datos): Year
    {
        $model                =     ModelV::findOrFail($datos['model_id']);

        $year                 =     new Year();
        $year->description    =     mb_strtoupper($datos['description'], 'UTF-8');
        $year->model_id       =     $datos['model_id'];
        $year->brand_id       =     $model->brand_id;
        $year->save();

        return $year;
    }

    public function getYear(int $id): Year
    {
        $year = Year::with(['model.brand'])->findOrFail($id);

        return $year;
    }

    public function update(int $id, array $datos): Year
    {
        $datos = collect($datos)->mapWithKeys(function ($value, $key) {
            if (str_ends_with($key, '_edit')) {
                $key = str_replace('_edit', '', $key);
            }
            return [$key => $value];
        })->toArray();

        $model                =   ModelV::findOrFail($datos['model_id']);

        $year                 =   Year::findOrFail($id);
        $year->description    =   mb_strtoupper($datos['description'], 'UTF-8');
        $year->model_id       =   $datos['model_id'];
        $year->brand_id       =   $model->brand_id;
        $year->update();

        return $year;
    }

    public function destroy(int $id): Year
    {
        $year = Year::findOrFail($id);
        $year->status = 'INACTIVE';
        $year->update();

        return $year;
    }
}
