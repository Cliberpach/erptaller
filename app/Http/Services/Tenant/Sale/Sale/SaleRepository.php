<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Models\Tenant\Sale;
use App\Models\Tenant\Sale\SaleService;
use App\Models\Tenant\SaleDetail;

class SaleRepository
{
    public function insertSale(array $dto): Sale
    {
        return Sale::create($dto);
    }

    public function insertSaleService(array $dto)
    {
        SaleService::insert($dto);
    }

    public function insertSaleProduct(array $dto)
    {
        SaleDetail::insert($dto);
    }
}
