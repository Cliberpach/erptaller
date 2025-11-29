<?php

namespace App\Http\Services\Tenant\WorkShop\Services;

use App\Almacenes\Producto;
use App\Http\Controllers\UtilController;
use App\Http\Services\Landlord\WorkShop\Brands\BrandService;
use App\Http\Services\Landlord\WorkShop\Models\ModelService;
use App\Models\Tenant\WorkShop\Brand;
use App\Models\Tenant\WorkShop\Color;
use App\Models\Tenant\WorkShop\Service;
use App\Models\Tenant\WorkShop\Vehicle;
use Illuminate\Support\Collection;

class ServiceService
{
    private ServiceRepository $s_repository;
    private BrandService $s_brand;
    private ModelService $s_model;
    private ServiceDto $s_dto;

    public function __construct()
    {
        $this->s_repository =   new ServiceRepository();
        $this->s_brand      =   new BrandService();
        $this->s_model      =   new ModelService();
        $this->s_dto        =   new ServiceDto();
    }

    public function store(array $data): Service
    {
        $dto        =   $this->s_dto->getDtoStore($data);

        $service    =   $this->s_repository->insertService($dto);
        return $service;
    }

    public function searchPlate(string $placa)
    {
        //======= BUSCAR SI EXISTE =======
        $vehicle   =   $this->s_repository->findPlate($placa);

        if ($vehicle) {
            return response()->json(['success' => true, 'data' => $vehicle, 'message' => 'CONSULTA COMPLETADA', 'origin' => 'BD']);
        }

        $res    =   UtilController::apiPlaca($placa);
        $_res   =   json_decode($res->getContent());

        if ($_res->success) {
            $data   =   $_res->data;
            if ($data->mensaje === 'SUCCESS') {
                $data_api  =   $data->data;
                $this->s_brand->insertIfNotExists($data_api->marca);
                $res_model          =   $this->s_model->insertIfNotExists($data_api->modelo, $data_api->marca);
                $_res->model_insert =   $res_model['model_insert'];
                $_res->model        =   $res_model['model'];
                $res->setContent(json_encode($_res));
            }
        }

        return $res;
    }

    public function update(array $data, int $id): Service
    {
        $dto        =   $this->s_dto->getDtoStore($data);
        $service    =   $this->s_repository->updateService($dto, $id);
        return $service;
    }

    public function destroy(int $id):Service
    {
        return $this->s_repository->destroy($id);
    }

    public function getService(int $id): Service
    {
        return $this->s_repository->getService($id);
    }
}
