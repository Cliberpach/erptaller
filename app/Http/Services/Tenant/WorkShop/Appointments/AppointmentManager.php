<?php

namespace App\Http\Services\Tenant\WorkShop\Appointments;

use App\Models\Tenant\WorkShop\Appointment\Appointment;
use App\Models\Tenant\WorkShop\Service;

class AppointmentManager
{
    private AppointmentService $s_service;

    public function __construct()
    {
        $this->s_service   =   new AppointmentService();
    }

    public function store(array $datos): Appointment
    {
        return $this->s_service->store($datos);
    }

    public function getService(int $id): Appointment
    {
        return $this->s_service->getService($id);
    }

    public function update(array $data, int $id): Appointment
    {
        return $this->s_service->update($data, $id);
    }

    public function destroy(int $id)
    {
        $this->s_service->destroy($id);
    }


}
