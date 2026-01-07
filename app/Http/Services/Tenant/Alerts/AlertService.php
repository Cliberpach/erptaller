<?php

namespace App\Http\Services\Tenant\Alerts;

use App\Models\Tenant\Alerts\Alert;

class AlertService
{
    private AlertDto $s_dto;
    private AlertRepository $s_repository;
    private AlertValidation $s_validation;

    public function __construct()
    {
        $this->s_repository =   new AlertRepository();
        $this->s_dto        =   new AlertDto($this->s_repository);
        $this->s_validation =   new AlertValidation($this->s_repository);
    }

    public function store(array $data): Alert
    {
        $dto    =   $this->s_dto->getDtoStore($data);
        $alert  =   $this->s_repository->store($dto);

        return $alert;
    }
}
