<?php

namespace App\Http\Services\Landlord\Maintenance\Company;

use App\Models\Tenant;

class CompanyManager
{
    protected CompanyService $s_company;

    public function __construct()
    {
        $this->s_company = new CompanyService();
    }

    public function store(array $data): Tenant
    {
        return $this->s_company->store($data);
    }

    public function edit(int $id)
    {
        return $this->s_company->edit($id);
    }

    public function update(array $data, int $id)
    {
        return $this->s_company->update($data, $id);
    }

    public function blockAccount(array $data, int $id)
    {
        return $this->s_company->blockAccount($data, $id);
    }
}
