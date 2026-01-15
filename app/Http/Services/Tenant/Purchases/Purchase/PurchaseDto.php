<?php

namespace App\Http\Services\Tenant\Purchases\Purchase;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant\PurchaseDocument;
use App\Models\Tenant\Sale\PaymentCondition\PaymentCondition;
use App\Models\Tenant\Warehouse;
use Carbon\Carbon;

class PurchaseDto
{

    public function getDtoStore(array $data): array
    {
        $dto    =   [];

        $supplier           =   Supplier::findOrFail($data['proveedor']);
        $warehouse          =   Warehouse::findOrFail(1);
        $montos             =   $this->calcularMontos($data['lst_purchase'], $data['igv_chk'] ?? null, $data['igv_value']);

        $dto['warehouse_id']                        =   $warehouse->id;
        $dto['warehouse_name']                      =   $warehouse->descripcion;
        $dto['delivery_date']                       =   $data['fecha_entrega'];
        $dto['supplier_id']                         =   $supplier->id;
        $dto['supplier_name']                       =   $supplier->name;
        $dto['supplier_type_document_abbreviation'] =   $supplier->type_document_abbreviation;
        $dto['supplier_document_number']            =   $supplier->document_number;
        $dto['currency']                            =   $data['moneda'];
        $dto['document_type']                       =   $data['tipo_doc'];
        $dto['serie']                               =   $data['serie'];
        $dto['correlative']                         =   $data['numero'];
        $dto['observation']                         =   $data['observation'];

        $dto['prices_with_igv']           =   isset($data['igv_chk']) ? 1 : 0;
        $dto['igv']                       =   $data['igv_value'];
        $dto['subtotal']                  =   $montos->subtotal;
        $dto['amount_igv']                =   $montos->monto_igv;
        $dto['total']                     =   $montos->total;

        //========= DATES =========
        $registration_date      =   now();
        $payment_condition      =   PaymentCondition::findOrFail($data['payment_condition_id']);
        $nro_days               =   (int) $payment_condition->nro_days;
        $expiration_date        =   $registration_date->copy()->addDays($nro_days);

        $dto['registration_date']       =   $registration_date;
        $dto['payment_condition_id']    =   $payment_condition->id;
        $dto['payment_condition_name']  =   $payment_condition->name;
        $dto['payment_condition_days']  =   $payment_condition->nro_days;
        $dto['expiration_date']         =   $expiration_date;

        $dto['payment_status']          =   $payment_condition->name === 'CONTADO' ? 'PAGADO' : 'PENDIENTE';
        return $dto;
    }

    public function getDtoDetail(array $lst_items, PurchaseDocument $purchase): array
    {
        $dto_detail =   [];
        foreach ($lst_items as  $item) {
            $_item  =   [];

            $product                        =   Product::findOrFail($item->product_id);
            $brand                          =   Brand::findOrFail($product->brand_id);
            $category                       =   Category::findOrFail($product->category_id);

            $_item['purchase_document_id']  =   $purchase->id;
            $_item['product_id']            =   $product->id;
            $_item['brand_id']              =   $brand->id;
            $_item['category_id']           =   $category->id;
            $_item['product_name']          =   $product->name;
            $_item['brand_name']            =   $brand->name;
            $_item['category_name']         =   $category->name;
            $_item['warehouse_id']          =   $purchase->warehouse_id;
            $_item['warehouse_name']        =   $purchase->warehouse_name;
            $_item['quantity']              =   $item->quantity;
            $_item['purchase_price']        =   $item->purchase_price;
            $_item['subtotal']              =   $item->quantity * $item->purchase_price;

            $dto_detail[]   =   $_item;
        }

        return $dto_detail;
    }

    public static function calcularMontos($lstPurchaseDocument, $igv_chk, $igv_value)
    {
        $subtotal   =   0;
        $monto_igv  =   0;
        $total      =   0;
        $valor_igv  =   $igv_value;

        if ($igv_chk) {
            foreach ($lstPurchaseDocument as $item) {
                $total  +=  (float)$item->total;
            }
            $subtotal    =   $total / ((100 + (float)$valor_igv) / 100);
            $monto_igv   =   $total - $subtotal;
        } else {
            //======= PRECIOS SIN IGV =======
            foreach ($lstPurchaseDocument as $item) {
                $subtotal  +=  (float)$item->total;
            }

            $monto_igv   =   ((float)$valor_igv / 100) * $subtotal;
            $total       =   $subtotal + $monto_igv;
        }

        return (object)[
            'subtotal' => $subtotal,
            'monto_igv' => $monto_igv,
            'total' => $total
        ];
    }
}
