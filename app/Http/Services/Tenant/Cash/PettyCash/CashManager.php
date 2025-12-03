<?php

namespace App\Http\Services\Tenant\Cash\PettyCash;

use App\Models\Tenant\Cash\PettyCash;

class CashManager
{
    private CashService  $s_cash;

     public function __construct()
    {
        $this->s_cash    =   new CashService();
    }

    public function store(array $data):PettyCash{
        return $this->s_cash->store($data);
    }

    public function update(array $data,int $id):PettyCash{
        return $this->s_cash->update($data,$id);
    }

    public function getCash(int $id):PettyCash{
        return $this->s_cash->getCash($id);
    }

    public function destroy(int $id):PettyCash{
        return $this->s_cash->destroy($id);
    }

    public function searchCashAvailable(array $data){
        return $this->s_cash->searchCashAvailable($data);
    }
}
