<?php

namespace App\Http\Services\Tenant\WorkShop\Quotes;

use Exception;

class QuoteValidation
{

    public function validationStore(array $data):array
    {
        $lst_products  =  json_decode($data['lst_products']);
        $lst_services  =  json_decode($data['lst_services']);

        if(count($lst_products) === 0){
            throw new Exception("DEBE INGRESAR POR LO MENOS UN PRODUCTO A LA COTIZACIÓN");
        }

        if(count($lst_services) === 0){
            throw new Exception("DEBE INGRESAR POR LO MENOS UN SERVICIO A LA COTIZACIÓN");
        }

        $data['lst_products'] =   $lst_products;
        $data['lst_services'] =   $lst_services;

        return $data;
    }

}
