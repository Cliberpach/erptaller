<?php

namespace App\Models\Tenant\WorkShop\Quote;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;
    protected $table = 'quotes';

    protected $fillable = [
        'warehouse_id',
        'warehouse_name',

        'customer_id',
        'customer_name',
        'customer_type_document_abbreviation',
        'customer_document_number',

        'vehicle_id',
        'plate',

        'total',
        'subtotal',
        'igv',

        'creator_user_id',
        'editor_user_id',
        'delete_user_id',

        'delete_user_name',
        'editor_user_name',
        'create_user_name',

        'status',

        'expiration_date',
        'days_validity',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (auth()->check()) {
                $quote->creator_user_id = auth()->id();
                $quote->create_user_name = auth()->user()->name;
            }
        });

        static::updating(function ($quote) {
            if (auth()->check()) {
                $quote->editor_user_id = auth()->id();
                $quote->editor_user_name = auth()->user()->name;
            }
            if ($quote->isDirty('status') && $quote->status === 'ANULADO') {
                if (auth()->check()) {
                    $quote->delete_user_id = auth()->id();
                    $quote->delete_user_name = auth()->user()->name;
                }
            }
        });
    }
}
