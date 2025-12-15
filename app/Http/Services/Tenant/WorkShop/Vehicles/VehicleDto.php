<?php

namespace App\Http\Services\Tenant\WorkShop\Vehicles;

use App\Models\Landlord\ModelV;

class VehicleDto
{

    public function getDtoStore(array $data):array
    {
        $dto                =   [];
        $dto['customer_id'] =   $data['client_id'];
        $dto['plate']       =   mb_strtoupper(str_replace(' ', '', trim($data['plate'])));
        $dto['model_id']    =   $data['model_id'];
        $dto['year_id']     =   $data['year_id'];
        $dto['observation'] =   isset($data['observation']) && trim($data['observation']) !== ''? trim($data['observation']): null;
        $dto['color_id']    =   $data['color_id'];

        $model  =   ModelV::findOrFail($data['model_id']);
        $dto['brand_id']    =   $model->brand_id;

        $dto['vin']         =   $data['vin'];
        $dto['serie']       =   $data['serie'];
        return $dto;
    }
}
