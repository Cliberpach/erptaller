<?php

namespace App\Http\Services\Tenant\Inventory\Kardex;

use App\Models\Product;
use App\Models\Tenant\NoteIncome;
use App\Models\Tenant\NoteIncomeDetail;
use App\Models\Tenant\PurchaseDocument;
use App\Models\Tenant\PurchaseDocumentDetail;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleDetail;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderProduct;
use Illuminate\Support\Facades\Auth;

class KardexDto
{
    public function getDtoFromNoteIncome(NoteIncome $note)
    {
        $dto            =   [];
        $lst_detail     =   NoteIncomeDetail::where('note_income_id', $note->id)->get();

        foreach ($lst_detail as $item) {

            $product                        =   Product::findOrFail($item->product_id);

            $_item                          =   [];
            $_item['note_income_id']        =   $note->id;
            $_item['type']                  =   'ENTRADA';
            $_item['document_serie']        =   'NI-' . str_pad($note->id, 8, '0', STR_PAD_LEFT);
            $_item['date']                  =   $note->created_at;
            $_item['warehouse_id']          =   $item->warehouse_id;
            $_item['warehouse_name']        =   $item->warehouse_name;
            $_item['product_id']            =   $item->product_id;
            $_item['category_id']           =   $item->category_id;
            $_item['brand_id']              =   $item->brand_id;
            $_item['product_name']          =   $item->product_name;
            $_item['product_unit']          =   'NIU';
            $_item['category_name']         =   $item->category_name;
            $_item['brand_name']            =   $item->brand_name;
            $_item['quantity']              =   $item->quantity;
            $_item['sale_price']            =   $product->sale_price;
            $_item['purchase_price']        =   $product->purchase_price;
            $_item['amount']                =   $item->quantity * $product->sale_price;
            $_item['creator_user_id']       =   Auth::user()->id;
            $_item['creator_user_name']     =   Auth::user()->name;

            $dto[]  =   $_item;
        }

        return $dto;
    }

    public function getDtoFromSale(Sale $sale)
    {
        $dto            =   [];
        $lst_detail     =   SaleDetail::where('sale_document_id', $sale->id)->get();

        foreach ($lst_detail as $item) {

            $product                        =   Product::findOrFail($item->product_id);
            $warehouse                      =   Warehouse::findOrFail($item->warehouse_id);

            $_item                          =   [];
            $_item['sale_id']               =   $sale->id;
            $_item['type']                  =   'SALIDA';
            $_item['document_serie']        =   $sale->serie . '-' . $sale->correlative;
            $_item['date']                  =   $sale->created_at;
            $_item['warehouse_id']          =   $item->warehouse_id;
            $_item['warehouse_name']        =   $warehouse->descripcion;
            $_item['product_id']            =   $item->product_id;
            $_item['category_id']           =   $item->category_id;
            $_item['brand_id']              =   $item->brand_id;
            $_item['product_name']          =   $item->product_name;
            $_item['product_unit']          =   $item->product_unit;
            $_item['category_name']         =   $item->category_name;
            $_item['brand_name']            =   $item->brand_name;
            $_item['quantity']              =   $item->quantity;
            $_item['sale_price']            =   $item->price_sale;
            $_item['purchase_price']        =   $product->purchase_price;
            $_item['amount']                =   $item->amount;
            $_item['creator_user_id']       =   Auth::user()->id;
            $_item['creator_user_name']     =   Auth::user()->name;
            $_item['customer_id']           =   $sale->customer_id;
            $_item['customer_name']         =   $sale->customer_name;
            $_item['customer_type_document_abbreviation']   =   $sale->customer_type_document;
            $_item['customer_document_number']              =   $sale->customer_document_number;

            $dto[]  =   $_item;
        }

        return $dto;
    }

    /**
     * Kardex de ANULACIÓN de venta interna: mismas filas que getDtoFromSale (wh, prod,
     * qty, precios, document_serie = serie-correlative, sale_id) pero type = 'ENTRADA'
     * (la devolución es el espejo invertido de la SALIDA de la emisión).
     */
    public function getDtoFromSaleAnulacion(Sale $sale)
    {
        $dto    =   $this->getDtoFromSale($sale);
        foreach ($dto as &$row) {
            $row['type']    =   'ENTRADA';
        }

        return $dto;
    }

    public function getDtoFromPurchase(PurchaseDocument $purchase)
    {
        $dto            =   [];
        $lst_detail     =   PurchaseDocumentDetail::where('purchase_document_id', $purchase->id)->get();

        foreach ($lst_detail as $item) {

            $product                        =   Product::findOrFail($item->product_id);

            $_item                          =   [];
            $_item['purchase_id']          =   $purchase->id;
            $_item['type']                  =   'ENTRADA';
            $_item['document_serie']        =   'C-' . str_pad($purchase->id, 8, '0', STR_PAD_LEFT);
            $_item['date']                  =   $purchase->created_at;
            $_item['warehouse_id']          =   $item->warehouse_id;
            $_item['warehouse_name']        =   $item->warehouse_name;
            $_item['product_id']            =   $item->product_id;
            $_item['category_id']           =   $item->category_id;
            $_item['brand_id']              =   $item->brand_id;
            $_item['product_name']          =   $item->product_name;
            $_item['product_unit']          =   'NIU';
            $_item['category_name']         =   $item->category_name;
            $_item['brand_name']            =   $item->brand_name;
            $_item['quantity']              =   $item->quantity;
            $_item['sale_price']            =   $product->sale_price;
            $_item['purchase_price']        =   $item->purchase_price;
            $_item['amount']                =   $item->subtotal;
            $_item['creator_user_id']       =   $purchase->creator_user_id;
            $_item['creator_user_name']     =   $purchase->creator_user_name;

            $dto[]  =   $_item;
        }

        return $dto;
    }

    public function getDtoFromWorkOrder(WorkOrder $work_order)
    {
        $dto            =   [];
        $lst_detail     =   WorkOrderProduct::where('work_order_id', $work_order->id)->get();

        foreach ($lst_detail as $item) {

            $product                        =   Product::findOrFail($item->product_id);

            $_item                          =   [];
            $_item['work_order_id']         =   $work_order->id;
            $_item['type']                  =   'SALIDA';
            $_item['document_serie']        =   'OT-' . str_pad($work_order->id, 8, '0', STR_PAD_LEFT);
            $_item['date']                  =   $work_order->created_at;
            $_item['warehouse_id']          =   $item->warehouse_id;
            $_item['warehouse_name']        =   $item->warehouse_name;
            $_item['product_id']            =   $item->product_id;
            $_item['category_id']           =   $item->category_id;
            $_item['brand_id']              =   $item->brand_id;
            $_item['product_name']          =   $item->product_name;
            $_item['product_unit']          =   $item->product_unit;
            $_item['category_name']         =   $item->category_name;
            $_item['brand_name']            =   $item->brand_name;
            $_item['quantity']              =   $item->quantity;
            $_item['sale_price']            =   $item->price_sale;
            $_item['purchase_price']        =   $product->purchase_price;
            $_item['amount']                =   $item->amount;
            $_item['creator_user_id']       =   $work_order->creator_user_id;
            $_item['creator_user_name']     =   $work_order->create_user_name;

            $dto[]  =   $_item;
        }

        return $dto;
    }
}
