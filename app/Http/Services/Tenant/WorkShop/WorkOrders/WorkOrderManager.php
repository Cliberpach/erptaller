<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Models\Tenant\WorkShop\Quote\Quote;

class WorkOrderManager
{
    private WorkOrderService $s_order;

    public function __construct()
    {
        $this->s_order   =   new WorkOrderService();
    }

    public function store(array $datos): Quote
    {
        return $this->s_order->store($datos);
    }

    public function getQuote(int $id): Quote
    {
        return $this->s_order->getQuote($id);
    }

    public function update(array $data, int $id): Quote
    {
        return $this->s_order->update($data, $id);
    }

    public function destroy(int $id)
    {
        $this->s_order->destroy($id);
    }

}
