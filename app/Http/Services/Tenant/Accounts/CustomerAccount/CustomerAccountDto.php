<?php

namespace App\Http\Services\Tenant\Accounts\CustomerAccount;

use App\Models\Company;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use Illuminate\Http\UploadedFile;

class CustomerAccountDto
{
    private CustomerAccountRepository $s_repository;

    public function __construct($_s_repository)
    {
        $this->s_repository =   $_s_repository;
    }

    public function getDtoFromWorkOrder($data): array
    {
        $dto    =   [];

        $work_order  =   WorkOrder::findOrFail($data['work_order_id']);

        $dto['work_order_id']   =   $work_order->id;
        $dto['document_number'] =   'OT-' . $work_order->id;
        $dto['document_date']   =   $work_order->created_at;
        $dto['amount']          =   $work_order->total;
        $dto['balance']         =   $work_order->total;

        return $dto;
    }

    public function getDtoPay($data)
    {
        $dto    =   [];
        $carpet_company =   Company::findOrFail(1)->files_route;

        $dto['customer_account_id'] =   $data['id'];
        $dto['petty_cash_book_id']  =   $data['petty_cash_book_id'];
        $dto['date']                =   $data['fecha'];
        $dto['observation']         =   $data['observacion'];
        $dto['total']               =   $data['cantidad'];
        $dto['payment_method_id']   =   $data['modo_pago'];
        $dto['cash']                =   $data['efectivo_venta'];
        $dto['amount']              =   $data['importe_venta'];
        $dto['balance']             =   $data['balance'];

        $file   =   $data['imagen']??null;
        if ($file instanceof UploadedFile && $file->isValid()) {
            $extension = $file->getClientOriginalExtension();
            $next_id_pay                =   $this->s_repository->getNexIdPay($dto['customer_account_id']);
            $filename                   =   $dto['customer_account_id'] . '_' . $next_id_pay . '.' . $extension;
            $dto['img_route']           =   "storage/{$carpet_company}/customer_accounts/images/{$filename}";
            $dto['img_name']            =   $filename;
        }



        return $dto;
    }
}
