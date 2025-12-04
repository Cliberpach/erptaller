<?php

namespace App\Http\Services\Tenant\Accounts\CustomerAccount;

use App\Models\Tenant\Accounts\CustomerAccount;

class CustomerAccountRepository
{
    public function insertCustomerAccount(array $dto)
    {
        return CustomerAccount::create($dto);
    }
}
