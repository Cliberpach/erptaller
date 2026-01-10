<?php

namespace App\Http\Services\Tenant\WorkShop\Appointments;

use App\Models\Landlord\Customer;
use App\Models\Landlord\ModelV;
use App\Models\Tenant\WorkShop\Vehicle;

class AppointmentDto
{

    public function getDtoStore(array $data): array
    {
        $dto = [];

        $dto['name'] = mb_strtoupper(
            trim($data['name']),
            'UTF-8'
        );

        $dto['description'] = isset($data['description'])
            ? mb_strtoupper(trim($data['description']), 'UTF-8')
            : null;

        $dto['type_calendar'] = $data['type_calendar'];

        $dto['start_date'] = $data['start_date'];
        $dto['start_time'] = $data['start_time'];

        $dto['end_date'] = $data['end_date'];
        $dto['end_time'] = $data['end_time'];

        $dto['location'] = $data['location'] ?? null;

        $dto['full_day'] = $data['full_day'] ?? false;

        $customer                                       =   Customer::findOrFail($data['customer_id']);
        $dto['customer_id']                             =   $customer->id;
        $dto['customer_name']                           =   $customer->name;
        $dto['customer_type_document_abbreviation']     =   $customer->type_document_abbreviation;
        $dto['customer_document_number']                =   $customer->document_number;

        $vehicle            =   Vehicle::findOrFail($data['vehicle_id']);
        $dto['vehicle_id']  =   $vehicle->id;
        $dto['plate']       =   $vehicle->plate;

        return $dto;
    }
}
