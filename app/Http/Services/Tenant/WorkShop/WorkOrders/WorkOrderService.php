<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderDto;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderRepository;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderValidation;
use App\Models\Tenant\WorkShop\Quote\Quote;

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

    public function store(array $data): Quote
    {
        $data           =   $this->s_validation->validationStore($data);
        $dto            =   $this->s_dto->getDtoStore($data);

        $quote      =   $this->s_repository->insertQuote($dto);
        $this->s_repository->insertQuoteDetail($data['lst_products'],$data['lst_services'], $quote);

        return $quote;
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

    public function getQuote(int $id): Quote
    {
        return $this->s_repository->getQuote($id);
    }
}
