<?php

namespace App\Http\Services\Tenant\WorkShop\Vehicles;

use App\Models\Tenant\WorkShop\Vehicle;

class VehicleManager
{
    private VehicleService $s_vehicle;

    public function __construct(){
        $this->s_vehicle   =   new VehicleService();
    }

    public function store(array $datos):Vehicle{
        return $this->s_vehicle->store($datos);
    }


    public function update (array $data,int $id):Vehicle{
        return $this->s_vehicle->update($data,$id);
    }

    public function destroy(int $id){
        $this->s_vehicle->destroy($id);
    }

    public function searchPlate(string $placa){
        return $this->s_vehicle->searchPlate($placa);
    }

}
