<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Product;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\WorkShop\Service;
use App\Models\Tenant\WorkShop\Vehicle;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class WorkOrderDto
{
    public function getDtoStore(array $data): array
    {
        $dto                    =   [];

        $warehouse              =   Warehouse::findOrFail($data['warehouse_id']);
        $dto['warehouse_id']    =   $warehouse->id;
        $dto['warehouse_name']  =   $warehouse->descripcion;

        $customer                                   =   Customer::findOrFail($data['client_id']);
        $dto['customer_id']                         =   $customer->id;
        $dto['customer_type_document_abbreviation'] =   $customer->type_document_abbreviation;
        $dto['customer_document_number']            =   $customer->document_number;
        $dto['customer_name']                       =   mb_strtoupper(trim($customer->name));

        $dto['vehicle_id']  =   $data['vehicle_id'];
        $vehicle            =   Vehicle::findOrFail($dto['vehicle_id']);
        $dto['plate']       =   $vehicle->plate;
        $dto['fuel_level']  =   $data['fuel_level'];

        //======== AMOUNTS ======
        $dto_amounts        =   $this->calculateAmounts($data['lst_products'], $data['lst_services']);
        $dto['total']       =   $dto_amounts['total'];
        $dto['subtotal']    =   $dto_amounts['subtotal'];
        $dto['igv']         =   $dto_amounts['igv'];

        //======= QUOTE ==========
        $dto['quote_id']    =   $data['quote_id'] ?? null;

        //========= CONFIGURATION ==========
        $dto['validation_stock']    =   $data['validation_stock'];

        return $dto;
    }

    public function getDtoOrderProduct($item, WorkOrder $work_order): array
    {
        $dto = [];
        $dto['work_order_id']   =   $work_order->id;
        $dto['warehouse_id']    =   $work_order->warehouse_id;
        $dto['warehouse_name']  =   $work_order->warehouse_name;

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

    public function getDtoOrderService($item, WorkOrder $work_order): array
    {
        $dto = [];
        $dto['work_order_id']   =   $work_order->id;

        $service                =   Service::findOrFail($item->id);
        $dto['service_id']      =   $service->id;
        $dto['service_name']    =   $service->name;

        $dto['quantity']        =   $item->quantity;
        $dto['price_sale']      =   $item->sale_price;
        $dto['amount']          =   $item->total;
        return $dto;
    }

    public function getDtoInventory(array $lst_items, WorkOrder $work_order): array
    {
        $items   =   [];
        foreach ($lst_items as $item) {
            $inventory  =   GeneralTableDetail::where('id', $item)->where('status', 'ACTIVO')->first();
            $_item      =   [
                'work_order_id'     =>  $work_order->id,
                'inventory_id'      =>  $item,
                'inventory_name'    =>  $inventory->name,
            ];
            $items[]    =   $_item;
        }
        return $items;
    }

    public function getDtoTechnicians(array $lst_items, WorkOrder $work_order): array
    {
        $items   =   [];
        foreach ($lst_items as $item) {
            $user  =   User::where('id', $item)->where('status', 'ACTIVO')->first();
            $_item      =   [
                'work_order_id'     =>  $work_order->id,
                'technical_id'     =>  $item,
                'technical_name'    =>  $user->name,
            ];
            $items[]    =   $_item;
        }
        return $items;
    }

    public function getDtoOrderImages(array $lst_items, WorkOrder $work_order): array
    {
        $carpet_company =   Company::findOrFail(1)->files_route;
        $path = public_path("storage/{$carpet_company}/work_orders/images/");
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $dtoImages = [];

        $count  =   0;
        foreach ($lst_items as $index => $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {

                $extension = $file->getClientOriginalExtension();
                $filename = $work_order->id . '_' . $count . '.' . $extension;
                $file->move($path, $filename);

                $dtoImages[] = [
                    'work_order_id' => $work_order->id,
                    'img_route'     => "storage/{$carpet_company}/work_orders/images/{$filename}",
                    'img_name'      => $filename,
                ];
                $count++;
            }
        }

        return $dtoImages;
    }

    public function getDtoWorkImage(int $id, array $data)
    {
        return [
            'work_order_id' => $id,
            'img_name'      => $data['name'],
            'img_route'     => $data['route'],
        ];
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
