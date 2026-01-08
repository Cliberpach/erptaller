<?php

namespace App\Http\Services\Tenant\Alerts;

use Carbon\Carbon;

class AlertDto
{

    public function getDtoStore(array $data)
    {
        $dto    =   [];
        $dto['name']                =   mb_strtoupper($data['name'], 'UTF-8');
        $dto['description']         =   isset($data['description']) ? mb_strtoupper($data['description'], 'UTF-8') : null;
        $dto['object_id']           =   $data['object_id'];
        $dto['type_object']         =   $data['type_object'];
        $dto['notice_date']         =   $data['notice_date'];
        $dto['advance_days']        =   $data['advance_days'];
        $dto['advance_date']        =   Carbon::parse($data['notice_date'])->subDays((int) $data['advance_days'])->format('Y-m-d');
        return $dto;
    }
}
