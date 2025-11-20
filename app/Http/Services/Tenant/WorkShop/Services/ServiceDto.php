<?php

namespace App\Http\Services\Tenant\WorkShop\Services;

use App\Models\Landlord\ModelV;

class ServiceDto
{

    public function getDtoStore(array $data): array
    {
        $dto                =   [];
        $dto['name']        =   mb_strtoupper(trim($data['name']));
        $dto['description'] =   mb_strtoupper(trim($data['description']));
        $dto['price']       =   mb_strtoupper(str_replace(' ', '', trim($data['price'])));

        return $dto;
    }
}
