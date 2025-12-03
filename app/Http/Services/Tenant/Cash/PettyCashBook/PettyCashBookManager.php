<?php

namespace App\Http\Services\Tenant\Cash\PettyCashBook;

use App\Models\Tenant\Cash\PettyCashBook;

class PettyCashBookManager
{
    private PettyCashBookService $s_cashbook;

     public function __construct()
    {
        $this->s_cashbook    =   new PettyCashBookService();
    }

    public function openPettyCash(array $data):PettyCashBook{
        return $this->s_cashbook->openPettyCash($data);
    }

    public function getPdfOne(array $data){
        return $this->s_cashbook->getPdfOne($data);
    }

}
