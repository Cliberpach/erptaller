<?php

namespace App\Models\Tenant\WorkShop\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderProduct extends Model
{
    use HasFactory;
    protected $table = 'work_orders_products';

    protected $fillable = [
        'work_order_id',
        'warehouse_id',
        'product_id',
        'category_id',
        'brand_id',
        'warehouse_name',
        'product_code',
        'product_unit',
        'product_description',
        'product_name',
        'category_name',
        'brand_name',
        'quantity',
        'price_sale',
        'amount',
        'status',

        'invoiced',
        'invoiced_sale_id',
        'invoiced_sale_serie',

        'created_at',
        'updated_at',
    ];
}
