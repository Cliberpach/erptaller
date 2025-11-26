<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Models\Tenant\WorkShop\Quote\Quote;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderImage;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderInventory;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderProduct;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderService;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderTechnical;

class WorkOrderRepository
{

    private WorkOrderDto $s_dto;

    public function __construct()
    {
        $this->s_dto    =   new WorkOrderDto();
    }

    public function insertWorkOrder(array $dto): WorkOrder
    {
        return WorkOrder::create($dto);
    }

    public function insertWorkOrderDetail(array $lst_products, array $lst_services, WorkOrder $work_order)
    {
        foreach ($lst_products as $item) {
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

    public function updateQuote(array $dto, int $id): Quote
    {
        $quote    =   Quote::findOrFail($id);
        $quote->update($dto);
        return $quote;
    }

    public function destroy(int $id): Quote
    {
        $quote            =   Quote::findOrFail($id);
        $quote->status    =   'INACTIVE';
        $quote->save();
        return $quote;
    }

    public function getWorkOrder(int $id): array
    {
        $order          =   WorkOrder::findOrFail($id);
        $products       =   WorkOrderProduct::where('work_order_id',$id)->get();
        $services       =   WorkOrderService::where('work_order_id',$id)->get();
        $inventory      =   WorkOrderInventory::where('work_order_id',$id)->get();
        $technicians    =   WorkOrderTechnical::where('work_order_id',$id)->get();
        $images         =   WorkOrderImage::where('work_order_id',$id)->get();

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
}
