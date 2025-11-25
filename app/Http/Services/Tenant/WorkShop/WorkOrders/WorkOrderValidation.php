<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Models\Tenant\WorkShop\Quote\Quote;
use Exception;

class WorkOrderValidation
{

    public function validationStore(array $data):array
    {
        $lst_products  =  json_decode($data['lst_products']);
        $lst_services  =  json_decode($data['lst_services']);

        if(count($lst_products) === 0 && count($lst_services) === 0){
            throw new Exception("DEBE INGRESAR POR LO MENOS UN PRODUCTO O SERVICIO A LA COTIZACIÓN");
        }

        $data['lst_products'] =   $lst_products;
        $data['lst_services'] =   $lst_services;

        return $data;
    }

    public function validationUpdate(array $data,int $id):array
    {
        $data = $this->validationStore($data);

        $quote  =   Quote::findOrFail($id);
        if($quote->status === 'ANULADO'){
            throw new Exception("NO SE PUEDE MODIFICAR UNA COTIZACIÓN ANULADA");
        }

        if($quote->status === 'EXPIRADO'){
            throw new Exception("NO SE PUEDE MODIFICAR UNA COTIZACIÓN EXPIRADA");
        }

        return $data;

    }

}
