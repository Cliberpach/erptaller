<?php

namespace App\Http\Controllers;

use App\Models\Landlord\Company;
use App\Models\Tenant\WorkShop\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class FormatController extends Controller
{

    public static function getFormatInitialVehicle(?int $vehicle_id): array
    {
        $vehicle    =   Vehicle::from('vehicles as v')
            ->join('erptaller.models as m', 'm.id', 'v.model_id')
            ->join('erptaller.brandsv as b', 'b.id', 'v.brand_id')
            ->where('v.id', $vehicle_id)
            ->select(
                'v.id',
                'm.description as model_name',
                'v.plate',
                'b.description as brand_name'
            )->first();

        if (!$vehicle) {
            return [];
        }

        $vehicle_formatted = [
            'id'        => $vehicle->id,
            'text' => $vehicle->plate,
            'subtext'     => $vehicle->brand_name . '-' . $vehicle->model_name,
        ];

        return $vehicle_formatted;
    }

    public static function getFormatInitialCustomer(int $customer_id): array
    {
        $customer   =   DB::connection('landlord')
            ->table('customers as c')
            ->select(
                'c.id',
                DB::raw('CONCAT(c.type_document_abbreviation,":",c.document_number,"-",c.name) as full_name'),
                'c.email'
            )
            ->where('c.id', $customer_id)
            ->first();

        if (!$customer) {
            return [];
        }

        $customer_formatted = [
            'id'        => $customer->id,
            'full_name' => $customer->full_name,
            'email'     => $customer->email,
        ];

        return $customer_formatted;
    }

    public static function formatLstProducts(array $items): array
    {
        $formatted = [];

        foreach ($items as $item) {
            $formatted[] = [
                'warehouse_id'=>$item['warehouse_id'],
                'id' => $item['product_id'],
                'name' => $item['product_name'],
                'category_name' => $item['category_name'],
                'brand_name' => $item['brand_name'],
                'sale_price' => $item['price_sale'],
                'quantity' => $item['quantity'],
                'total' => $item['amount'],
            ];
        }

        return $formatted;
    }

    public static function formatLstServices(array $items): array
    {
        $formatted = [];

        foreach ($items as $item) {
            $formatted[] = [
                'id' => $item['service_id'],
                'name' => $item['service_name'],
                'sale_price' => $item['price_sale'],
                'quantity' => $item['quantity'],
                'total' => $item['amount'],
            ];
        }

        return $formatted;
    }

    public static function formatLstInventory(array $items): array
    {
        $formatted = [];
        foreach ($items as $item) {
            $formatted[]    =   $item['inventory_id'];
        }
        return $formatted;
    }

    public static function formatLstTechnicians(array $items): array
    {
        $formatted = [];
        foreach ($items as $item) {
            $formatted[]    =   $item['technical_id'];
        }
        return $formatted;
    }

    public static function formatLstImages(array $items): array
    {
        $formatted = [];
        foreach ($items as $item) {
            $formatted[] = [
                'id' => $item['id'],
                'work_order_id' => $item['work_order_id'],
                'img_route' => asset($item['img_route']),
                'img_name' => $item['img_name'],
            ];
        }
        return $formatted;
    }
}
