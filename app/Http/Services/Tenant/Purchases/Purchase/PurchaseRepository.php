<?php

namespace App\Http\Services\Tenant\Purchases\Purchase;

use App\Models\Tenant\Alerts\Alert;
use App\Models\Tenant\PurchaseDocument;
use App\Models\Tenant\PurchaseDocumentDetail;

class PurchaseRepository
{
    public function store(array $dto): PurchaseDocument
    {
        return PurchaseDocument::create($dto);
    }

    public function storeDetail(array $dto)
    {
        PurchaseDocumentDetail::insert($dto);
    }
}
