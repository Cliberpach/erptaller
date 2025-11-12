<?php

namespace App\Http\Services\Tenant\Inventory\Kardex;

use App\Models\Tenant\Kardex;

class KardexService
{

    public function __construct() {}

    public function store($document, $item, string $type, string $document_name)
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
            $kardex->document           =   $document->serie.'-'.$document->correlative;
            $kardex->product_name       =   $item->product_name;
            $kardex->brand_name         =   $item->brand_name;
            $kardex->category_name      =   $item->category_name;
            $kardex->sale_document_id   =   $document->id;
            $kardex->user_recorder_id   =   $document->user_recorder_id;
            $kardex->user_recorder_name =   $document->user_recorder_name;
            $kardex->registration_date  =   $document->created_at;
            $kardex->save();
        }
    }
}
