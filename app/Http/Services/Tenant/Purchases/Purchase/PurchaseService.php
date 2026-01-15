<?php

namespace App\Http\Services\Tenant\Purchases\Purchase;

use App\Http\Services\Tenant\Accounts\SupplierAccount\SupplierAccountService;
use App\Http\Services\Tenant\Inventory\Kardex\KardexService;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Tenant\Alerts\Alert;
use App\Models\Tenant\PurchaseDocument;

class PurchaseService
{
    private PurchaseDto $s_dto;
    private PurchaseRepository $s_repository;
    private PurchaseValidation $s_validation;
    private WarehouseProductService $s_warehouse;
    private KardexService $s_kardex;
    private SupplierAccountService $s_account;


    public function __construct()
    {
        $this->s_repository =   new PurchaseRepository();
        $this->s_dto        =   new PurchaseDto($this->s_repository);
        $this->s_validation =   new PurchaseValidation($this->s_repository);
        $this->s_warehouse  =   new WarehouseProductService();
        $this->s_kardex     =   new KardexService();
        $this->s_account    =   new SupplierAccountService();
    }

    public function store(array $data): PurchaseDocument
    {
        $data       =   $this->s_validation->validationStore($data);
        $dto        =   $this->s_dto->getDtoStore($data);
        $item       =   $this->s_repository->store($dto);

        $dto_detail =   $this->s_dto->getDtoDetail($data['lst_purchase'], $item);
        $this->s_repository->storeDetail($dto_detail);

        $this->s_warehouse->increaseLstStock($dto_detail);

        $this->s_kardex->storeFromPurchase($item);

        if ($item->payment_condition_id && $item->payment_condition_name !== 'CONTADO') {
            $this->s_account->store(['purchase_id' => $item->id]);
        }
      
        return $item;
    }
}
