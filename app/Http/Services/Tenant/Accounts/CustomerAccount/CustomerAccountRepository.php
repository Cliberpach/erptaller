<?php

namespace App\Http\Services\Tenant\Accounts\CustomerAccount;

use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Accounts\CustomerAccountDetail;
use App\Models\Tenant\Sale;

class CustomerAccountRepository
{
    public function insertCustomerAccount(array $dto)
    {
        return CustomerAccount::create($dto);
    }

    public function insertPay($dto)
    {
        return CustomerAccountDetail::create($dto);
    }

    public function findCustomerAccount(int $id)
    {
        return CustomerAccount::findOrFail($id);
    }

    public function updateCustomerAccount(int $id, array $dto)
    {
        $customer_account = CustomerAccount::findOrFail($id);
        $customer_account->update($dto);
        return $customer_account;
    }

    public function getNexIdPay(int $customer_account_id)
    {
        return CustomerAccountDetail::where('customer_account_id', $customer_account_id)->count() + 1;
    }

    public function setPaymentStatus(int $id)
    {
        $customer_account   =   CustomerAccount::findOrFail($id);
        if ($customer_account->balance == 0 && $customer_account->sale_id) {
            $sale                   =   Sale::findOrFail($customer_account->sale_id);
            $sale->payment_status   =   'PAGADO';
            $sale->save();
        }
    }

    public function findByWorkOrder(int $work_order_id)
    {
        return CustomerAccount::where('work_order_id', $work_order_id)->first();
    }
}
