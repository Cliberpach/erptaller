<?php

namespace App\Http\Services\Tenant\Purchases\Purchase;

use App\Models\Tenant\Alerts\Alert;
use App\Models\Tenant\PurchaseDocument;

class PurchaseManager
{
    private PurchaseService $s_service;

    public function __construct(){
        $this->s_service    =   new PurchaseService();
    }

    public function store(array $data):PurchaseDocument
    {
        return $this->s_service->store($data);
    }
}
