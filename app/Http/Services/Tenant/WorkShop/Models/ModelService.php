<?php

namespace App\Http\Services\Tenant\WorkShop\Models;

use App\Almacenes\Producto;
use App\Models\Tenant\WorkShop\Brand;
use App\Models\Tenant\WorkShop\Color;
use App\Models\Tenant\WorkShop\ModelV;
use Illuminate\Support\Collection;

class ModelService
{
    public function store(array $datos): ModelV
    {
        $modelo                 =   new ModelV();
        $modelo->description    =   mb_strtoupper($datos['description'], 'UTF-8');
        $modelo->brand_id       =   mb_strtoupper($datos['brand_id'], 'UTF-8');
        $modelo->save();

        return $modelo;
    }

    public function getModel(int $id): ModelV
    {
        return ModelV::findOrFail($id);
    }

    public function update(int $id, array $datos): ModelV
    {
        $datos = collect($datos)->mapWithKeys(function ($value, $key) {
            if (str_ends_with($key, '_edit')) {
                $key = str_replace('_edit', '', $key);
            }
            return [$key => $value];
        })->toArray();


        $modelo                 =   ModelV::findOrFail($id);
        $modelo->description    =   mb_strtoupper($datos['description'], 'UTF-8');
        $modelo->brand_id       =   mb_strtoupper($datos['brand_id'], 'UTF-8');
        $modelo->update();

        return $modelo;
    }

    public function destroy(int $id): ModelV
    {
        $modelo = ModelV::findOrFail($id);
        $modelo->status = 'INACTIVE';
        $modelo->update();

        return $modelo;
    }
}
