<?php

namespace App\Http\Services\Tenant\WorkShop\Brands;

use App\Almacenes\Producto;
use App\Models\Tenant\WorkShop\Brand;
use App\Models\Tenant\WorkShop\Color;
use Illuminate\Support\Collection;

class BrandService
{
    public function store(array $datos): Brand
    {
        $marca              =   new Brand();
        $marca->description =   mb_strtoupper($datos['description'], 'UTF-8');
        $marca->save();

        return $marca;
    }

    public function getMarca(int $id): Brand
    {
        return Brand::findOrFail($id);
    }

    public function update(int $id, array $datos): Brand
    {
        $datos = collect($datos)->mapWithKeys(function ($value, $key) {
            if (str_ends_with($key, '_edit')) {
                $key = str_replace('_edit', '', $key);
            }
            return [$key => $value];
        })->toArray();


        $marca                  =   Brand::findOrFail($id);
        $marca->description     =   mb_strtoupper($datos['description'], 'UTF-8');
        $marca->update();
        return $marca;
    }

    public function destroy(int $id): Brand
    {
        $marca = Brand::findOrFail($id);
        $marca->status = 'INACTIVE';
        $marca->update();

        return $marca;
    }
}
