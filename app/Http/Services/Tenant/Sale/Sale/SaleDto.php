<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Http\Controllers\Tenant\NumberToLettersController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant\Cash\PettyCash;
use App\Models\Tenant\Cash\PettyCashBook;
use App\Models\Tenant\Sale;
use App\Models\Tenant\WorkShop\Service;
use Illuminate\Support\Facades\Auth;

class SaleDto
{

    private CorrelativeService $s_correlative;

    public function __construct()
    {
        $this->s_correlative    =   new CorrelativeService();
    }

    public function getDtoStoreFromOrder(array $data)
    {
        $dto    =   [];

        $customer                           =   $data['customer'];
        $dto['customer_id']                 =   $customer->id;
        $dto['customer_name']               =   $customer->name;
        $dto['customer_type_document']      =   $customer->type_document_abbreviation;
        $dto['customer_document_number']    =   $customer->document_number;
        $dto['customer_document_code']      =   $customer->type_document_code;
        $dto['customer_phone']              =   $customer->phone;

        $user_recorder                      =   Auth::user();
        $dto['user_recorder_id']           =   $user_recorder->id;
        $dto['user_recorder_name']          =   $user_recorder->name;

        $petty_cash                         =   PettyCash::where('type', 'FICTICIO')->first();
        $dto['petty_cash_id']               =   $petty_cash->id;
        $dto['petty_cash_name']             =   $petty_cash->name;

        $petty_cash_book                    =   PettyCashBook::where('type', 'FICTICIO')->first();
        $dto['petty_cash_book_id']          =   $petty_cash_book->id;

        $invoice_type                       =   $data['invoice_type'];
        $dto['type_sale_id']                =   $invoice_type->id;
        $dto['type_sale_code']              =   $invoice_type->symbol;
        $dto['type_sale_name']              =   $invoice_type->name;

        $dto['igv_percentage']              =   $data['igv_percentage'];
        $dto['subtotal']                    =   $data['subtotal'];
        $dto['igv_amount']                  =   $data['igv_amount'];
        $dto['total']                       =   $data['total'];

        $legend                 =   NumberToLettersController::numberToLetters($data['total']);
        $dto['legend']          =   $legend;

        $dto['method_pay_id_1'] =   1;
        $dto['amount_pay_1']    =   0;

        $dto['method_pay_id_2'] =   null;
        $dto['amount_pay_2']    =   null;

        $data_correlative       =   $this->s_correlative->getCorrelative($dto['type_sale_id']);
        $dto['correlative']     =   $data_correlative->correlative;
        $dto['serie']           =   $data_correlative->serie;

        $dto['type']            =   "PRODUCTOS";


        $dto['work_order_id']   =   $data['work_order_id']??null;

        return $dto;
    }

    public function getDtoServices(array $data, Sale $sale)
    {
        $dto    =   [];

        foreach ($data as $item) {
            $service                          =     Service::findOrFail($item->id);
            $s_dto['sale_document_id']        =     $sale->id;
            $s_dto['service_id']              =     $service->id;
            $s_dto['service_code']            =     $service->id;
            $s_dto['service_unit']            =     'NIU';
            $s_dto['service_description']     =     $service->id;
            $s_dto['service_name']            =     $service->name;
            $s_dto['quantity']                =     $item->quantity;
            $s_dto['price_sale']              =     $item->sale_price;
            $s_dto['amount']                  =     $item->quantity * $item->sale_price;

            $s_dto['mto_valor_unitario']     =   (float)($item->sale_price / 1.18);
            $s_dto['mto_valor_venta']        =   (float)($s_dto['amount'] / 1.18);
            $s_dto['mto_base_igv']           =   (float)($s_dto['amount'] / 1.18);
            $s_dto['porcentaje_igv']         =   $sale->igv_percentage;
            $s_dto['igv']                    =   (float)($s_dto['amount']) - (float)($s_dto['amount'] / 1.18);
            $s_dto['tip_afe_igv']            =   10;
            $s_dto['total_impuestos']        =   (float)($s_dto['amount']) - (float)($s_dto['amount'] / 1.18);
            $s_dto['mto_precio_unitario']    =   (float)($item->sale_price);

            $dto[]  =   $s_dto;
        }

        return $dto;
    }

     public function getDtoProducts(array $data, Sale $sale)
    {
        $dto    =   [];

        foreach ($data as $item) {
            $product                            =     Product::findOrFail($item->id);
            $category                           =     Category::findOrFail($product->category_id);
            $brand                              =     Brand::findOrFail($product->brand_id);

            $s_dto['sale_document_id']        =     $sale->id;
            $s_dto['warehouse_id']              =     $item->warehouse_id;
            $s_dto['product_id']                =     $item->id;
            $s_dto['category_id']               =     $product->category_id;
            $s_dto['brand_id']                  =     $product->brand_id;
            $s_dto['product_code']              =     'P-'.$product->id;
            $s_dto['product_unit']              =     'NIU';
            $s_dto['product_description']       =     $product->name;
            $s_dto['product_name']              =     $product->name;
            $s_dto['category_name']             =     $category->name;
            $s_dto['brand_name']                =     $brand->name;
            $s_dto['quantity']                  =     $item->quantity;

            $s_dto['price_sale']                =     $item->sale_price;
            $s_dto['amount']                    =     $item->quantity * $item->sale_price;

            $s_dto['mto_valor_unitario']     =   (float)($item->sale_price / 1.18);
            $s_dto['mto_valor_venta']        =   (float)($s_dto['amount'] / 1.18);
            $s_dto['mto_base_igv']           =   (float)($s_dto['amount'] / 1.18);
            $s_dto['porcentaje_igv']         =   $sale->igv_percentage;
            $s_dto['igv']                    =   (float)($s_dto['amount']) - (float)($s_dto['amount'] / 1.18);
            $s_dto['tip_afe_igv']            =   10;
            $s_dto['total_impuestos']        =   (float)($s_dto['amount']) - (float)($s_dto['amount'] / 1.18);
            $s_dto['mto_precio_unitario']    =   (float)($item->sale_price);

            $dto[]  =   $s_dto;
        }

        return $dto;
    }
}
