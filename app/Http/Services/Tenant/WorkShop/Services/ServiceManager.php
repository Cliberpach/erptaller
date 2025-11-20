<?php

namespace App\Http\Services\Tenant\WorkShop\Services;

use App\Almacenes\Producto;
use App\Models\Tenant\WorkShop\Brand;
use App\Models\Tenant\WorkShop\Color;
use App\Models\Tenant\WorkShop\Service;
use App\Models\Tenant\WorkShop\Vehicle;
use Illuminate\Support\Collection;

class ServiceManager
{
    private ServiceService $s_service;

    public function __construct()
    {
        $this->s_service   =   new ServiceService();
    }

    public function store(array $datos): Service
    {
        return $this->s_service->store($datos);
    }

    public function getService(int $id): Service
    {
        return $this->s_service->getService($id);
    }

    public function update(array $data, int $id): Service
    {
        return $this->s_service->update($data, $id);
    }

    public function destroy(int $id)
    {
        $this->s_service->destroy($id);
    }

    public function searchPlate(string $placa)
    {
        return $this->s_service->searchPlate($placa);
    }
}
