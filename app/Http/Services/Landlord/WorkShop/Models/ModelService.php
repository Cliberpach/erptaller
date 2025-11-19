<?php

namespace App\Http\Services\Landlord\WorkShop\Models;

use App\Models\Landlord\Brand;
use App\Models\Landlord\ModelV;

class ModelService
{
    private ModelRepository $s_repository;

    public function __construct(){
        $this->s_repository =   new ModelRepository();
    }

    public function store(array $data): ModelV
    {
        return $this->s_repository->insertModel($data);
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

    public function insertIfNotExists(string $model,string $brand):array
    {
        $model_exists   =   $this->s_repository->findModel($model,$brand);
        if (!$model_exists) {
            $brand_id   =   Brand::where('description',$brand)->where('status','ACTIVE')->first()->id;
            $data       =   ['description' => $model,'brand_id'=>$brand_id];
            $model      =   $this->store($data);
            return ['model_insert'=>true,'model'=>$model];
        }
        return ['model_insert'=>false,'model'=>$model_exists];
    }
}
