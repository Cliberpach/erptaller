<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\WorkShop\Quote\Quote;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderProduct;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderService;
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
            throw new Exception("DEBE INGRESAR POR LO MENOS UN PRODUCTO O SERVICIO A LA ORDEN DE TRABAJO");
        }

        $quote_id   =   $data['quote_id'] ?? null;
        if ($quote_id) {
            $quote  =   Quote::findOrFail($quote_id);
            if ($quote->order_id) {
                throw new Exception("LA COTIZACIÓN YA FUE CONVERTIDA EN LA ORDEN OT-" . $quote->order_id);
            }
        }

        $data['lst_products']       =   $lst_products;
        $data['lst_services']       =   $lst_services;
        $data['validation_stock']   =   Configuration::findOrFail(2)->property === '1' ? true : false;

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

        $data['validation_stock']           =   Configuration::findOrFail(2)->property === '1' ? true : false;
        $data['validation_stock_preview']   =   $order->validation_stock?true:false;

        return $data;
    }

    public function validationProduct($item, $validation_stock)
    {
        if ($validation_stock == '1') {
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

    public function validationInvoice(array $data){
        $work_order_id  =   $data['work_order_id'];
        $lst_products   =   json_decode($data['lst_products']);
        $lst_services   =   json_decode($data['lst_services']);

        if(count($lst_products) === 0 && count($lst_services) === 0){
            throw new Exception("LA ORDEN DE TRABAJO ESTÁ VACÍA");
        }

        foreach ($lst_products as $item) {
            $exists =   WorkOrderProduct::where('work_order_id',$work_order_id)
                        ->where('product_id',$item->id)
                        ->where('invoiced',true)
                        ->exists();

            if($exists){
                throw new Exception($item->name.',YA FUE FACTURADO EN ESTA ORDEN: OT-'.$work_order_id->id);
            }
        }

        foreach ($lst_services as $item) {
            $exists =   WorkOrderService::where('work_order_id',$work_order_id)
                        ->where('service_id',$item->id)
                        ->where('invoiced',true)
                        ->exists();

            if($exists){
                throw new Exception($item->name.',YA FUE FACTURADO EN ESTA ORDEN: OT-'.$work_order_id->id);
            }
        }

        $data['lst_products']   =   $lst_products;
        $data['lst_services']   =   $lst_services;

        return $data;

    }
}
