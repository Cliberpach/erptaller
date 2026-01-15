<?php

namespace App\Http\Services\Tenant\Accounts\SupplierAccount;

use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Accounts\CustomerAccountDetail;
use App\Models\Tenant\Accounts\SupplierAccount\SupplierAccount;
use App\Models\Tenant\Accounts\SupplierAccount\SupplierAccountDetail;
use App\Models\Tenant\PurchaseDocument;
use App\Models\Tenant\Sale;

class SupplierAccountRepository
{
    public function store(array $dto):SupplierAccount
    {
        return SupplierAccount::create($dto);
    }

    public function insertPay($dto)
    {
        return SupplierAccountDetail::create($dto);
    }

    public function findAccount(int $id):SupplierAccount
    {
        return SupplierAccount::findOrFail($id);
    }

    public function updateAccount(int $id, array $dto):SupplierAccount
    {
        $account = SupplierAccount::findOrFail($id);
        $account->update($dto);
        return $account;
    }

    public function getNexIdPay(int $id)
    {
        return SupplierAccountDetail::where('supplier_account_id', $id)->count() + 1;
    }

    public function setPaymentStatus(int $id)
    {
        $account   =   SupplierAccount::findOrFail($id);
        if ($account->balance == 0) {
            $purchase                   =   PurchaseDocument::findOrFail($account->purchase_id);
            $purchase->payment_status   =   'PAGADO';
            $purchase->save();
        }
    }

    public function findByWorkOrder(int $work_order_id)
    {
        return CustomerAccount::where('work_order_id', $work_order_id)->first();
    }
}
