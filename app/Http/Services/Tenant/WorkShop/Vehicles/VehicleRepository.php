<?php

namespace App\Http\Services\Tenant\WorkShop\Vehicles;

use App\Models\Tenant\WorkShop\Vehicle;
use App\Support\Placa;

class VehicleRepository
{

    public function findPlate(string $placa): ?Vehicle
    {
        // Match TOLERANTE: normaliza ambos lados (clave sin guion/espacios) para encontrar
        // la placa aunque se haya tecleado en otro formato. No migra datos.
        $clave = Placa::claveComparacion($placa);

        return Vehicle::whereRaw("REPLACE(REPLACE(UPPER(plate), '-', ''), ' ', '') = ?", [$clave])
            ->where('status', 'ACTIVO')
            ->first();
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
