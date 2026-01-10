<?php

namespace App\Http\Services\Tenant\WorkShop\Appointments;

use App\Almacenes\Producto;
use App\Http\Controllers\UtilController;
use App\Models\Tenant\WorkShop\Appointment\Appointment;

class AppointmentService
{
    private AppointmentRepository $s_repository;
    private AppointmentDto $s_dto;

    public function __construct()
    {
        $this->s_repository =   new AppointmentRepository();
        $this->s_dto        =   new AppointmentDto();
    }

    public function store(array $data): Appointment
    {
        $dto        =   $this->s_dto->getDtoStore($data);
        $item       =   $this->s_repository->store($dto);
        return $item;
    }


    public function update(array $data, int $id): Appointment
    {
        $dto        =   $this->s_dto->getDtoStore($data);
       
        $item    =   $this->s_repository->update($dto, $id);
        return $item;
    }

    public function destroy(int $id): Appointment
    {
        return $this->s_repository->destroy($id);
    }

    public function getService(int $id): Appointment
    {
        return $this->s_repository->getService($id);
    }
}
