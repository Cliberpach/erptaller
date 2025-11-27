<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use Exception;

class WorkOrderValidation
{
    private WarehouseProductService $s_warehouse_product;

    public function __construct()
    {
        $this->s_warehouse_product  =   new WarehouseProductService();
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

        $order  =   WorkOrder::findOrFail($id);
        if ($order->status === 'ANULADO') {
            throw new Exception("NO SE PUEDE MODIFICAR UNA ORDEN DE TRABAJO ANULADA");
        }

        if ($order->status === 'EXPIRADO') {
            throw new Exception("NO SE PUEDE MODIFICAR UNA ORDEN DE TRABAJO EXPIRADA");
        }

        return $data;
    }

    public function validationProduct($item)
    {

        $product_bd =   $this->s_warehouse_product->getProductStock($item->warehouse_id, $item->id);

        if (!$product_bd) {
            throw new Exception($item->name . ', PRODUCTO NO ENCONTRADO EN BD');
        }

        if ((float)$item->quantity > (float)$product_bd->stock) {
            throw new Exception(
                "⚠ Stock insuficiente para: {$item->name}\n"
                    . "Stock disponible: " . round($product_bd->stock, 2) . "\n"
                    . "Cantidad solicitada: " . round($item->quantity, 2)
            );
        }
  
    }
}
