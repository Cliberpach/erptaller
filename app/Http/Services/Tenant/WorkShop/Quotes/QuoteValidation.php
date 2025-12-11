<?php

namespace App\Http\Services\Tenant\WorkShop\Quotes;

use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\WorkShop\Quote\Quote;
use Exception;

class QuoteValidation
{

    private WarehouseProductService $s_warehouse_product;

    public function __construct(WarehouseProductService $s_warehouse_product)
    {
        $this->s_warehouse_product = $s_warehouse_product;
    }

    public function validationStore(array $data): array
    {
        $lst_products  =  json_decode($data['lst_products']);
        $lst_services  =  json_decode($data['lst_services']);

        if (count($lst_products) === 0 && count($lst_services) === 0) {
            throw new Exception("DEBE INGRESAR POR LO MENOS UN PRODUCTO O SERVICIO A LA COTIZACIÓN");
        }

        $data['lst_products'] =   $lst_products;
        $data['lst_services'] =   $lst_services;

        return $data;
    }

    public function validationUpdate(array $data, int $id): array
    {
        $data = $this->validationStore($data);

        $quote  =   Quote::findOrFail($id);
        if ($quote->status === 'ANULADO') {
            throw new Exception("NO SE PUEDE MODIFICAR UNA COTIZACIÓN ANULADA");
        }

        if ($quote->status === 'EXPIRADO') {
            throw new Exception("NO SE PUEDE MODIFICAR UNA COTIZACIÓN EXPIRADA");
        }

        return $data;
    }

    public function validationConvertOrderCreate(array $data)
    {
        $products                   =   $data['products'];
        $services                   =   $data['services'];

        $configuration              =   Configuration::findOrFail(2);
        if ($configuration->property === 1) {
            $lst_products_validated     =   collect($this->s_warehouse_product->validatedStock($products->toArray()));
            $count_products_validated   =   $lst_products_validated->where('valid', true)->count();

            if ($count_products_validated === 0 && count($services) === 0) {
                throw new Exception("NO EXISTEN PRODUCTOS Y SERVICIOS EN LA COTIZACIÓN");
            }
            $data_validated =    [
                'valid_products'    =>  $lst_products_validated->where('valid', true)->toArray()
            ];
        } else {
            $data_validated =    [
                'valid_products'    =>  $products->toArray()
            ];
        }

        $data_validated['configuration']    =   $configuration;

        return $data_validated;
    }
}
