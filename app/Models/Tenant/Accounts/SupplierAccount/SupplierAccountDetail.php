<?php

namespace App\Models\Tenant\Accounts\SupplierAccount;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierAccountDetail extends Model
{
    use HasFactory;

    protected $guarded = [''];
    protected $table = 'supplier_accounts_details';

    protected $fillable = [
        'supplier_account_id',
        'petty_cash_book_id',
        'date',
        'observation',
        'img_route',
        'total',
        'payment_method_id',
        'cash',
        'amount',
        'paid',
        'balance',
        'img_name',
        'payment_method_name',
        'creator_user_id',
        'creator_user_name'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->creator_user_id = auth()->id();
                $model->creator_user_name = auth()->user()->name;
            }
        });

        /*static::updating(function ($model) {
            if (auth()->check()) {
                $model->editor_user_id = auth()->id();
                $model->editor_user_name = auth()->user()->name;
            }
            if ($model->isDirty('status') && $model->status === 'ANULADO') {
                if (auth()->check()) {
                    $model->delete_user_id = auth()->id();
                    $model->delete_user_name = auth()->user()->name;
                }
            }
        });*/
    }
}
