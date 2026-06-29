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
            // Forma plana unificada (vehiculo{}) ADITIVA: se conserva 'data' (legacy) para
            // no romper los callers que aún leen la forma anidada (migran en Capa 2).
            return response()->json([
                'success'  => true,
                'data'     => $vehicle,
                'message'  => 'CONSULTA COMPLETADA',
                'origin'   => 'BD',
                'vehiculo' => $this->mapVehiculo([
                    'placa'    => $vehicle->plate,
                    'marca'    => optional($vehicle->brand)->description,
                    'modelo'   => optional($vehicle->model)->description,
                    'anio'     => optional($vehicle->year)->description,
                    'color'    => optional($vehicle->color)->description,
                    'vin'      => $vehicle->vin,
                    'serie'    => $vehicle->serie,
                    'motor'    => $vehicle->motor,
                    'model_id' => $vehicle->model_id,
                    'year_id'  => $vehicle->year_id,
                    'color_id' => $vehicle->color_id,
                ]),
            ]);
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

                // Forma plana unificada (misma que la rama BD). anio/year_id null: el API no
                // los trae. model_id/color_id salen de insertIfNotExists (creados/encontrados).
                $_res->vehiculo = $this->mapVehiculo([
                    'placa'    => $placa,
                    'marca'    => $data_api->marca ?? null,
                    'modelo'   => $data_api->modelo ?? null,
                    'anio'     => null,
                    'color'    => $data_api->color ?? null,
                    'vin'      => $data_api->vin ?? null,
                    'serie'    => $data_api->serie ?? null,
                    'motor'    => $data_api->motor ?? null,
                    'model_id' => $res_model['model']->id ?? null,
                    'year_id'  => null,
                    'color_id' => isset($res_color) ? ($res_color['color']->id ?? null) : null,
                ]);

                $res->setContent(json_encode($_res));
            }
        }

        return $res;
    }

    /**
     * Forma PLANA unificada de un vehículo para el prefill (rama BD y API la comparten).
     * 10 claves fijas: texto para mostrar + ids para los selects.
     */
    private function mapVehiculo(array $v): array
    {
        return [
            'placa'    => $v['placa'] ?? null,
            'marca'    => $v['marca'] ?? null,
            'modelo'   => $v['modelo'] ?? null,
            'anio'     => $v['anio'] ?? null,
            'color'    => $v['color'] ?? null,
            'vin'      => $v['vin'] ?? null,
            'serie'    => $v['serie'] ?? null,
            'motor'    => $v['motor'] ?? null,
            'model_id' => $v['model_id'] ?? null,
            'year_id'  => $v['year_id'] ?? null,
            'color_id' => $v['color_id'] ?? null,
        ];
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
