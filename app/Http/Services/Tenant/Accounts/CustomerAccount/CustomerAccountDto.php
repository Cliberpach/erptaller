<?php
namespace App\Http\Services\Tenant\Accounts\CustomerAccount;

use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;

class CustomerAccountDto
{

    public function getDtoFromWorkOrder($data):array{
        $dto    =   [];

        $work_order  =   WorkOrder::findOrFail($data['work_order_id']);

        $dto['work_order_id']   =   $work_order->id;
        $dto['document_number'] =   'OT-'.$work_order->id;
        $dto['document_date']   =   $work_order->created_at;
        $dto['amount']          =   $work_order->total;
        $dto['balance']         =   $work_order->total;

        return $dto;
    }

}