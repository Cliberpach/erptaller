<?php

namespace App\Models\Tenant\Accounts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAccountDetail extends Model
{
    use HasFactory;

    protected $guarded = [''];
    protected $table = 'customer_accounts_details';

    protected $fillable = [
        'customer_account_id',
        'petty_cash_book_id',
        'date',
        'observation',
        'img_route',
        'total',
        'payment_method_id',
        'cash',
        'amount',
        'balance',
        'img_name'
    ];
}
