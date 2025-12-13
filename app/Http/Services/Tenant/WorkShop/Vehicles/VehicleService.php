<?php

namespace App\Http\Services\Tenant\WorkShop\Vehicles;

use App\Http\Controllers\UtilController;
use App\Http\Services\Landlord\WorkShop\Brands\BrandService;
use App\Http\Services\Landlord\WorkShop\Colors\ColorService;
use App\Http\Services\Landlord\WorkShop\Models\ModelService;
use App\Models\Tenant\WorkShop\Vehicle;

class VehicleService
{
    private VehicleRepository $s_repository;
    private BrandService $s_brand;
    private ModelService $s_model;
    private VehicleDto $s_dto;
    private ColorService $s_color;

    public function __construct()
    {
        $this->s_repository =   new VehicleRepository();
        $this->s_brand      =   new BrandService();
        $this->s_model      =   new ModelService();
        $this->s_dto        =   new VehicleDto();
        $this->s_color      =   new ColorService();
    }

    public function store(array $data): Vehicle
    {
        $dto        =   $this->s_dto->getDtoStore($data);
        $vehicle    =   $this->s_repository->insertVehicle($dto);
        return $vehicle;
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

                if (strlen($data_api->color) > 0) {
                    $res_color          =   $this->s_color->insertIfNotExists($data_api->color);
                    $_res->color_insert =   $res_color['color_insert'];
                    $_res->color        =   $res_color['color'];
                } else {
                    $_res->color_insert =   false;
                    $_res->color        =   null;
                }

                $res->setContent(json_encode($_res));
            }
        }

        return $res;
    }

    public function update(array $data, int $id): Vehicle
    {
        $dto        =   $this->s_dto->getDtoStore($data);
        $vehicle    =   $this->s_repository->updateVehicle($dto, $id);
        return $vehicle;
    }

    public function destroy(int $id)
    {
        $this->s_repository->destroy($id);
    }
}
