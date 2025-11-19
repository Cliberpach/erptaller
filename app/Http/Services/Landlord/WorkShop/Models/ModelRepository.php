<?php

namespace App\Http\Services\Landlord\WorkShop\Models;

use App\Models\Landlord\ModelV;
use Illuminate\Support\Facades\DB;

class ModelRepository
{
    public function insertModel(array $data): ModelV
    {
        $model                  =   new ModelV();
        $model->description     =   mb_strtoupper($data['description'], 'UTF-8');
        $model->brand_id        =   $data['brand_id'];
        $model->save();

        return $model;
    }

    public function findModel(string $model, string $brand)
    {
        return DB::table('erptaller.models as m')
            ->join('erptaller.brandsv as b', 'b.id', 'm.brand_id')
            ->where('m.description', $model)
            ->where('b.description', $brand)
            ->where('m.status', 'ACTIVE')
            ->select(
                'm.id',
                'm.description',
                'm.brand_id'
            )
            ->first();
    }
}
