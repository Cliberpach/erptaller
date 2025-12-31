<?php

namespace App\Http\Services\Tenant\Inventory\Kardex;


class KardexManager
{
    protected KardexService $s_kardex;

     public function __construct()
    {
        $this->s_kardex    =   new KardexService();
    }

    public function store(){

    }
}
