<?php

namespace App\Http\Services\Tenant\WorkShop\Vehicles;

use App\Almacenes\Producto;
use App\Models\Tenant\WorkShop\Brand;
use App\Models\Tenant\WorkShop\Color;
use App\Models\Tenant\WorkShop\Vehicle;
use Illuminate\Support\Collection;

class VehicleManager
{
    private VehicleService $s_vehicle;

    public function __construct(){
        $this->s_vehicle   =   new VehicleService();
    }

    public function store(array $datos):Vehicle{
        return $this->s_vehicle->store($datos);
    }

    public function getMarca(int $id):Vehicle{
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
