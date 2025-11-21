<?php

namespace App\Models\Tenant\WorkShop;


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

            if (empty($quote->name)) {
                $quote->name = $quote->plate;
            }

            if (empty($quote->status)) {
                $quote->status = 'ACTIVE';
            }
        });

        static::updating(function ($quote) {
            if (auth()->check()) {
                $quote->editor_user_id = auth()->id();
                $quote->editor_user_name = auth()->user()->name;
            }
            if ($quote->isDirty('status') && $quote->status === 'INACTIVE') {
                if (auth()->check()) {
                    $quote->delete_user_id = auth()->id();
                    $quote->delete_user_name = auth()->user()->name;
                }
            }
        });
    }
}
