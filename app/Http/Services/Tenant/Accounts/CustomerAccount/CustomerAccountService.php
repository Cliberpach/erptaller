<?php

namespace App\Http\Services\Tenant\Accounts\CustomerAccount;

use App\Http\Services\Tenant\Accounts\CustomerAccount\CustomerAccountDto;
use App\Models\Tenant\Accounts\CustomerAccount;

class CustomerAccountService
{
    private CustomerAccountDto $s_dto;
    private CustomerAccountRepository $s_repository;

    public function __construct()
    {
        $this->s_dto    =   new CustomerAccountDto();
        $this->s_repository =   new CustomerAccountRepository();
    }

    public function store(array $data): CustomerAccount
    {
        $dto   =    $this->s_dto->getDtoFromWorkOrder($data);
        $customer_account   =   $this->s_repository->insertCustomerAccount($dto);

        return $customer_account;
    }

    public function storePago(array $data): CustomerAccount
    {
        dd($data)
    }
}
