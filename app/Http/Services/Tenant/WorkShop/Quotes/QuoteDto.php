<?php

namespace App\Http\Services\Tenant\WorkShop\Quotes;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Product;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\WorkShop\Quote\Quote;
use App\Models\Tenant\WorkShop\Service;
use DateTime;

class QuoteDto
{

    public function getDtoStore(array $data): array
    {
        $dto                =   [];

        $warehouse          =   Warehouse::findOrFail($data['warehouse_id']);
        $dto['warehouse_id']    =   $warehouse->id;
        $dto['warehouse_name']  =   $warehouse->descripcion;

        $customer                                   =   Customer::findOrFail($data['client_id']);
        $dto['customer_id']                         =   $customer->id;
        $dto['customer_type_document_abbreviation'] =   $customer->type_document_abbreviation;
        $dto['customer_document_number']            =   $customer->document_number;
        $dto['customer_name']                       =   mb_strtoupper(trim($customer->name));

        $dto['plate']       =   mb_strtoupper(trim($data['plate']));
        $dto['vehicle_id']  =   $data['vehicle_id'];

        $days_validity      =   0;
        if (isset($data['expiration_date'])) {
            $dto['expiration_date'] =   $data['expiration_date'];
            $date1                      =   new DateTime(date('Y-m-d'));
            $date2                      =   new DateTime($data['expiration_date']);
            $interval                   =   $date1->diff($date2);
            $days_validity              =   $interval->days;
            $dto['days_validity']       =   $days_validity;
        }

        //======== AMOUNTS ======
        $dto_amounts    =   $this->calculateAmounts($data['lst_products'], $data['lst_services']);
        $dto['total']       =   $dto_amounts['total'];
        $dto['subtotal']    =   $dto_amounts['subtotal'];
        $dto['igv']         =   $dto_amounts['igv'];

        return $dto;
    }

    public function getDtoQuoteProduct($item, Quote $quote): array
    {
        $dto = [];
        $dto['quote_id']        =   $quote->id;
        $dto['warehouse_id']    =   $quote->warehouse_id;
        $dto['warehouse_name']  =   $quote->warehouse_name;

        $product                =   Product::findOrFail($item->id);
        $dto['product_id']      =   $product->id;
        $dto['category_id']     =   $product->category_id;
        $dto['brand_id']        =   $product->brand_id;
        $dto['product_code']    =   $product->name;
        $dto['product_name']    =   $product->name;
        $dto['product_unit']    =   'NIU';
        $dto['product_description'] = $product->name;

        $category   =   Category::findOrFail($product->category_id);
        $dto['category_name']   =   $category->name;

        $brand  =   Brand::findOrFail($product->brand_id);
        $dto['brand_name']      =   $brand->name;

        $dto['quantity']        =   $item->quantity;
        $dto['price_sale']      =   $item->sale_price;
        $dto['amount']          =   $item->total;
        return $dto;
    }

    public function getDtoQuoteService($item, Quote $quote): array
    {
        $dto = [];
        $dto['quote_id']        =   $quote->id;

        $service                =   Service::findOrFail($item->id);
        $dto['service_id']      =   $service->id;
        $dto['service_name']    =   $service->name;

        $dto['quantity']        =   $item->quantity;
        $dto['price_sale']      =   $item->sale_price;
        $dto['amount']          =   $item->total;
        return $dto;
    }


    public function calculateAmounts(array $lst_products, array $lst_services): array
    {
        $total = 0;
        $igv   = 0;
        $subtotal = 0;
        $porcentaje_igv = Company::find(1)->igv;

        foreach ($lst_products as $product) {
            $total  +=  $product->total;
        }

        foreach ($lst_services as $service) {
            $total  +=  $service->total;
        }

        $subtotal   =   $total / (1 + ($porcentaje_igv / 100));
        $igv        =   $total - $subtotal;

        return [
            'total'     =>  $total,
            'subtotal'  =>  $subtotal,
            'igv'       =>  $igv
        ];
    }
}
