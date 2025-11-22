<?php

namespace App\Models\Tenant\WorkShop\Quote;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteService extends Model
{
    use HasFactory;
    protected $table = 'quotes_services';

    protected $fillable = [
        'quote_id',
        'service_id',
        'service_name',
        'quantity',
        'price_sale',
        'amount',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {});

        static::updating(function ($quote) {});
    }
}
