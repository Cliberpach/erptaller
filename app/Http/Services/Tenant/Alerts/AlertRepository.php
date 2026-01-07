<?php

namespace App\Http\Services\Tenant\Alerts;

use App\Models\Tenant\Alerts\Alert;

class AlertRepository
{
    public function store(array $dto):Alert
    {
        return Alert::create($dto);
    }
}
