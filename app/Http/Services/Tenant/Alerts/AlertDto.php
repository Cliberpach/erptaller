<?php

namespace App\Http\Services\Tenant\Alerts;

use App\Models\Company;
use App\Models\Tenant\Sale;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use Illuminate\Http\UploadedFile;

class AlertDto
{

    public function getDtoStore(array $data)
    {
        $dto    =   [];
        $dto['name']                =   $data['name'];
        $dto['description']         =   $data['description'];
        $dto['object_id']           =   $data['object_id'];
        $dto['type_object']         =   $data['type_object'];
        $dto['notice_date']         =   $data['notice_date'];
        $dto['advance_date']        =   $data['advance_date'];
        return $dto;
    }
}
