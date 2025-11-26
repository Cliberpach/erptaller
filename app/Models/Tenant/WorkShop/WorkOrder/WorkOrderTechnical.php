<?php

namespace App\Models\Tenant\WorkShop\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderTechnical extends Model
{
    use HasFactory;
    protected $table = 'work_orders_technicians';

    protected $fillable = [
        'work_order_id',
        'technical_id',
        'technical_name',
        'status',
        'created_at',
        'updated_at',
    ];
}
