<?php

namespace App\Http\Services\Tenant\Alerts;

use App\Models\Tenant\Alerts\Alert;

class AlertManager
{
    private AlertService $s_service;

    public function __construct(){
        $this->s_service    =   new AlertService();
    }

    public function store(array $data):Alert
    {
        return $this->s_service->store($data);
    }
}
