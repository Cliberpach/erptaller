<?php

namespace App\Models\Tenant\WorkShop\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderService extends Model
{
    use HasFactory;
    protected $table = 'work_orders_services';

    protected $fillable = [
        'work_order_id',
        'service_id',
        'service_name',
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
