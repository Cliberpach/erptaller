<?php

namespace App\Models\Tenant\WorkShop\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderInventory extends Model
{
    use HasFactory;
    protected $table = 'work_orders_inventory';

    protected $fillable = [
        'work_order_id',
        'inventory_id',
        'inventory_name',
        'status',
        'created_at',
        'updated_at',
    ];

   
}
