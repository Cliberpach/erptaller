<?php

namespace App\Http\Services\Tenant\Accounts\CustomerAccount;

use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Accounts\CustomerAccountDetail;

class CustomerAccountManager
{
    private CustomerAccountService  $s_account;

     public function __construct()
    {
        $this->s_account    =   new CustomerAccountService();
    }

    public function store(array $data):CustomerAccount{
        return $this->s_account->store($data);
    }

    public function storePago(array $data):CustomerAccountDetail{
        return $this->s_account->storePago($data);
    }

    public function pdfOne(int $id){
        return $this->s_account->pdfOne($id);
    }

}
