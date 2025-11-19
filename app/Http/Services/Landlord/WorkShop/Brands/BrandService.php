<?php

namespace App\Http\Services\Landlord\WorkShop\Brands;

use App\Models\Landlord\Brand;

class BrandService
{
    private BrandRepository $s_repository;

    public function __construct(){
        $this->s_repository =   new BrandRepository();
    }

    public function store(array $data): Brand
    {
        return $this->s_repository->insertBrand($data);
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

    public function insertIfNotExists(string $description){
        $brand_exists   =   $this->s_repository->findBrand($description);
        if(!$brand_exists){
            $data   =   ['description'=>$description];
            $this->store($data);
        }
    }
}
