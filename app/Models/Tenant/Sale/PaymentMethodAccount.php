<?php

namespace App\Models\Tenant\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodAccount extends Model
{
    use HasFactory;
    protected $table = 'payment_method_accounts';

    protected $fillable = [
        'payment_method_id',
        'bank_account_id',
    ];
}
