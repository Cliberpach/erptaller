<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Tenant\Sale;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderImage;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderInventory;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderProduct;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderService;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderTechnical;

class WorkOrderRepository
{
    private WorkOrderDto $s_dto;
    private WorkOrderValidation $s_validation;
    private WarehouseProductService $s_warehouse_product;

    public function __construct()
    {
        $this->s_dto    =   new WorkOrderDto();
        $this->s_validation =   new WorkOrderValidation();
        $this->s_warehouse_product  =   new WarehouseProductService();
    }

    public function insertWorkOrder(array $dto): WorkOrder
    {
        return WorkOrder::create($dto);
    }

    public function findWorkOrder(int $id)
    {
        return WorkOrder::findOrFail($id);
    }

    public function insertWorkOrderDetail(array $lst_products, array $lst_services, WorkOrder $work_order)
    {
        foreach ($lst_products as $item) {

            $this->s_validation->validationProduct($item, $work_order->validation_stock);

            if ($work_order->validation_stock) {
                $this->s_warehouse_product->decreaseStock($item->warehouse_id, $item->id, $item->quantity);
            }

            $dto_item = $this->s_dto->getDtoOrderProduct($item, $work_order);
            WorkOrderProduct::create($dto_item);
        }
        foreach ($lst_services as $item) {
            $dto_item = $this->s_dto->getDtoOrderService($item, $work_order);
            WorkOrderService::create($dto_item);
        }
    }

    public function insertWorkInventory(array $dto)
    {
        WorkOrderInventory::insert($dto);
    }

    public function insertWorkTechnicians(array $dto)
    {
        WorkOrderTechnical::insert($dto);
    }

    public function insertWorkImages(array $dto)
    {
        WorkOrderImage::insert($dto);
    }

    public function updateWorkOrder(array $dto, int $id): WorkOrder
    {
        $work_order    =   WorkOrder::findOrFail($id);
        $work_order->update($dto);
        return $work_order;
    }

    public function destroy(int $id): WorkOrder
    {
        $work_order            =   WorkOrder::findOrFail($id);
        $work_order->status    =   'ANULADO';
        $work_order->save();
        return $work_order;
    }

    public function finish(int $id): WorkOrder
    {
        $work_order            =   WorkOrder::findOrFail($id);
        $work_order->status    =   'FINALIZADO';
        $work_order->save();
        return $work_order;
    }

    public function deleteDetailProduct(int $id)
    {
        WorkOrderProduct::where('work_order_id', $id)->delete();
    }

    public function deleteDetailService(int $id)
    {
        WorkOrderService::where('work_order_id', $id)->delete();
    }

    public function deleteDetailInventory(int $id)
    {
        WorkOrderInventory::where('work_order_id', $id)->delete();
    }

    public function deleteDetailTechnical(int $id)
    {
        WorkOrderTechnical::where('work_order_id', $id)->delete();
    }

    public function deleteWorkImage(int $id)
    {

        WorkOrderImage::where('id', $id)->delete();
    }

    public function getWorkImageDetail(int $id)
    {
        return WorkOrderImage::where('work_order_id', $id)->get();
    }

    public function getWorkOrder(int $id): array
    {
        $order = WorkOrder::with([
            'vehicle.brand',
            'vehicle.model',
            'vehicle.year',
            'vehicle.color',
        ])->findOrFail($id);

        $products       =   WorkOrderProduct::where('work_order_id', $id)->get();
        $services       =   WorkOrderService::where('work_order_id', $id)->get();
        $inventory      =   WorkOrderInventory::where('work_order_id', $id)->get();
        $technicians    =   WorkOrderTechnical::where('work_order_id', $id)->get();
        $images         =   WorkOrderImage::where('work_order_id', $id)->get();

        $data   =   [
            'order' =>  $order,
            'products'  =>  $products,
            'services'  =>  $services,
            'inventory' =>  $inventory,
            'technicians'   =>  $technicians,
            'images'        =>  $images
        ];

        return $data;
    }

    public function countWorkImage(int $id)
    {
        return WorkOrderImage::where('work_order_id', $id)->count();
    }

    public function insertWorkImage(array $dto)
    {
        WorkOrderImage::create($dto);
    }

    public function getWorkProducts(int $id)
    {
        return WorkOrderProduct::where('work_order_id', $id)->get();
    }

      public function getWorkServices(int $id)
    {
        return WorkOrderService::where('work_order_id', $id)->get();
    }

    public function isWorkProductInvoiced(int $work_order_id, int $product_id): bool
    {
        $exists =   WorkOrderProduct::where('work_order_id', $work_order_id)
            ->where('product_id', $product_id)
            ->where('invoiced', true)
            ->exists();

        return $exists;
    }

    public function isWorkServiceInvoiced(int $work_order_id, int $service_id): bool
    {
        $exists =   WorkOrderService::where('work_order_id', $work_order_id)
            ->where('service_id', $service_id)
            ->where('invoiced', true)
            ->exists();

        return $exists;
    }

    public function setInvoicedWorkProducts(int $work_order_id, Sale $sale, array $lst_products)
    {
        foreach ($lst_products as $product) {
            WorkOrderProduct::where('work_order_id', $work_order_id)
                ->where('product_id', $product->id)
                ->update([
                    'invoiced' => true,
                    'invoiced_sale_id' => $sale->id,
                    'invoiced_sale_serie' => $sale->serie . '-' . $sale->correlative
                ]);
        }
    }

    public function setInvoicedWorkServices(int $work_order_id, Sale $sale, array $lst_services)
    {
        foreach ($lst_services as $service) {
            WorkOrderService::where('work_order_id', $work_order_id)
                ->where('service_id', $service->id)
                ->update([
                    'invoiced' => true,
                    'invoiced_sale_id' => $sale->id,
                    'invoiced_sale_serie' => $sale->serie . '-' . $sale->correlative
                ]);
        }
    }

     public function setWorkStatusInvoice(int $id,string $status): WorkOrder
    {
        $work_order                     =   WorkOrder::findOrFail($id);
        $work_order->status_invoice     =   $status;
        $work_order->save();
        return $work_order;
    }
}
