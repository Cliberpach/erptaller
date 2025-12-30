<?php

namespace App\Models\Tenant\Sale\PaymentCondition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCondition extends Model
{
    use HasFactory;
    protected $table = 'payment_conditions';

    protected $fillable = [
        'name',
        'type',
        'nro_days',
        'status',
        'created_at',
        'updated_at'
    ];
}
