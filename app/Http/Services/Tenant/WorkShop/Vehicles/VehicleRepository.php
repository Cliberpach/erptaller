<?php

namespace App\Http\Services\Tenant\WorkShop\Vehicles;

use App\Models\Tenant\WorkShop\Vehicle;

class VehicleRepository
{

    public function findPlate(string $placa): ?Vehicle
    {
        return Vehicle::where('plate', $placa)->where('status', 'ACTIVO')->first();
    }

    public function insertVehicle(array $dto): Vehicle
    {
        $vehicle    =   Vehicle::create($dto);
        return $vehicle->load(['brand', 'model']);
    }

    public function updateVehicle(array $dto,int $id): Vehicle
    {
        $vehicle    =   Vehicle::findOrFail($id);
        $vehicle->update($dto);
        return $vehicle;
    }

    public function destroy(int $id){
        $vehicle            =   Vehicle::findOrFail($id);
        $vehicle->status    =   'INACTIVE';
        $vehicle->save();
    }
}
