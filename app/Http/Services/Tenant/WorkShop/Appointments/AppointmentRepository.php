<?php

namespace App\Http\Services\Tenant\WorkShop\Appointments;

use App\Models\Tenant\WorkShop\Appointment\Appointment;
use App\Models\Tenant\WorkShop\Service;
use App\Models\Tenant\WorkShop\Vehicle;

class AppointmentRepository
{

    public function findPlate(string $placa): ?Service
    {
        return Service::where('plate', $placa)->where('status', 'ACTIVE')->first();
    }

    public function store(array $dto): Appointment
    {
        return Appointment::create($dto);
    }

    public function update(array $dto,int $id): Appointment
    {
        $item    =   Appointment::findOrFail($id);
        $item->update($dto);
        return $item;
    }

    public function destroy(int $id):Service{
        $service            =   Service::findOrFail($id);
        $service->status    =   'INACTIVE';
        $service->save();
        return $service;
    }

    public function getService(int $id):Service{
        $service    =   Service::findOrFail($id);
        return $service;
    }
}
