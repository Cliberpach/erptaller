<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderDto;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderRepository;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderValidation;
use App\Models\Tenant\WorkShop\Quote\Quote;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;

class WorkOrderService
{
    private WorkOrderRepository $s_repository;
    private WorkOrderDto $s_dto;
    private WorkOrderValidation $s_validation;

    public function __construct()
    {
        $this->s_repository =   new WorkOrderRepository();
        $this->s_dto        =   new WorkOrderDto();
        $this->s_validation =   new WorkOrderValidation();
    }

    public function store(array $data): WorkOrder
    {
        $data           =   $this->s_validation->validationStore($data);
        $dto            =   $this->s_dto->getDtoStore($data);

        $work_order     =   $this->s_repository->insertWorkOrder($dto);

        $dto_inventory      =   $this->s_dto->getDtoInventory($data['inventory_items'],$work_order);
        $dto_technicians    =   $this->s_dto->getDtoTechnicians($data['technicians'],$work_order);

        $this->s_repository->insertWorkOrderDetail($data['lst_products'],$data['lst_services'], $work_order);
        $this->s_repository->insertWorkInventory($dto_inventory);
        $this->s_repository->insertWorkTechnicians($dto_technicians);

        $dto_images =   $this->s_dto->getDtoOrderImages($data['vehicle_images'],$work_order);
        $this->s_repository->insertWorkImages($dto_images);
        return $work_order;
    }

    public function update(array $data, int $id): Quote
    {
        $data       =   $this->s_validation->validationUpdate($data, $id);
        $dto        =   $this->s_dto->getDtoStore($data);

        $quote      =   $this->s_repository->updateQuote($dto, $id);
        return $quote;
    }

    public function destroy(int $id):Quote
    {
        return $this->s_repository->destroy($id);
    }

    public function getWorkOrder(int $id): array
    {
        return $this->s_repository->getWorkOrder($id);
    }
}
