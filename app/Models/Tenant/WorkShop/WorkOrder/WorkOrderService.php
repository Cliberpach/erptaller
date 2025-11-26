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
        'created_at',
        'updated_at',
    ];
}
