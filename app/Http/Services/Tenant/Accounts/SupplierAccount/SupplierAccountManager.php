<?php

namespace App\Http\Services\Tenant\Accounts\SupplierAccount;

use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Accounts\CustomerAccountDetail;
use App\Models\Tenant\Accounts\SupplierAccount\SupplierAccount;
use App\Models\Tenant\Accounts\SupplierAccount\SupplierAccountDetail;

class SupplierAccountManager
{
    private SupplierAccountService  $s_account;

     public function __construct()
    {
        $this->s_account    =   new SupplierAccountService();
    }

    public function store(array $data):SupplierAccount{
        return $this->s_account->store($data);
    }

    public function storePago(array $data):SupplierAccountDetail{
        return $this->s_account->storePago($data);
    }

    public function pdfOne(int $id){
        return $this->s_account->pdfOne($id);
    }

}
