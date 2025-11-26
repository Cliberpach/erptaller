<?php

namespace App\Models\Tenant\WorkShop\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderImage extends Model
{
    use HasFactory;
    protected $table = 'work_orders_images';

    protected $fillable = [
        'work_order_id',
        'img_route',
        'img_name',
        'status',
        'created_at',
        'updated_at',
    ];
}
