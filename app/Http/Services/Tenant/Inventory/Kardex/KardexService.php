<?php

namespace App\Http\Services\Tenant\Inventory\Kardex;

use App\Models\Tenant\Kardex;
use App\Models\Tenant\NoteIncome;
use App\Models\Tenant\PurchaseDocument;
use App\Models\Tenant\Sale;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;

class KardexService
{
    private KardexDto $s_dto;
    private KardexRepository $s_repository;

    public function __construct()
    {
        $this->s_dto        =   new KardexDto();
        $this->s_repository =   new KardexRepository();
    }

    public function storeFromNoteIncome(NoteIncome $note_income)
    {
        $dto    =   $this->s_dto->getDtoFromNoteIncome($note_income);
        $this->s_repository->insertKardex($dto);
    }

    public function storeFromSale(Sale $sale)
    {
        $dto    =   $this->s_dto->getDtoFromSale($sale);
        $this->s_repository->insertKardex($dto);
    }

    /**
     * Anulación de venta interna: kardex ENTRADA (devolución), espejo invertido de la
     * SALIDA de la emisión. Mismas filas (wh/prod/qty/precios), atadas por sale_id.
     */
    public function storeFromSaleAnulacion(Sale $sale)
    {
        $dto    =   $this->s_dto->getDtoFromSaleAnulacion($sale);
        $this->s_repository->insertKardex($dto);
    }

    public function storeFromPurchase(PurchaseDocument $purchase)
    {
        $dto    =   $this->s_dto->getDtoFromPurchase($purchase);
        $this->s_repository->insertKardex($dto);
    }

    public function storeFromWorkOrder(WorkOrder $work_order)
    {
        $dto    =   $this->s_dto->getDtoFromWorkOrder($work_order);
        $this->s_repository->insertKardex($dto);
    }

    public function store_old($document, $item, string $type, string $document_name)
    {
        if ($document_name === 'NOTE INCOME') {
            $kardex                     =   new Kardex();
            $kardex->warehouse_id       =   $item->warehouse_id;
            $kardex->product_id         =   $item->product_id;
            $kardex->brand_id           =   $item->brand_id;
            $kardex->category_id        =   $item->category_id;
            $kardex->quantity           =   $item->quantity;
            $kardex->price_sale         =   null;
            $kardex->amount             =   null;
            $kardex->type               =   'IN';
            $kardex->document           =   'NI-' . $document->id;
            $kardex->product_name       =   $item->product_name;
            $kardex->brand_name         =   $item->brand_name;
            $kardex->category_name      =   $item->category_name;
            $kardex->note_income_id     =   $document->id;
            $kardex->user_recorder_id   =   $document->user_recorder_id;
            $kardex->user_recorder_name =   $document->user_recorder_name;
            $kardex->registration_date  =   $document->created_at;
            $kardex->save();
        }

        if ($document_name === 'SALE') {
            $kardex                     =   new Kardex();
            $kardex->warehouse_id       =   $item->warehouse_id;
            $kardex->product_id         =   $item->product_id;
            $kardex->brand_id           =   $item->brand_id;
            $kardex->category_id        =   $item->category_id;
            $kardex->quantity           =   $item->quantity;
            $kardex->price_sale         =   $item->price_sale;
            $kardex->amount             =   $item->amount;
            $kardex->type               =   'OUT';
            $kardex->document           =   $document->serie . '-' . $document->correlative;
            $kardex->product_name       =   $item->product_name;
            $kardex->brand_name         =   $item->brand_name;
            $kardex->category_name      =   $item->category_name;
            $kardex->sale_document_id   =   $document->id;
            $kardex->user_recorder_id   =   $document->user_recorder_id;
            $kardex->user_recorder_name =   $document->user_recorder_name;
            $kardex->registration_date  =   $document->created_at;
            $kardex->stock_previous     =   0;
            $kardex->stock_later        =   0;
            $kardex->save();
        }
    }
}
