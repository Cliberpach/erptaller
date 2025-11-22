<?php

namespace App\Models\Tenant\WorkShop\Quote;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteProduct extends Model
{
    use HasFactory;
    protected $table = 'quotes_products';

    protected $fillable = [
        'quote_id',
        'warehouse_id',
        'warehouse_name',
        'product_id',
        'category_id',
        'brand_id',
        'product_code',
        'product_unit',
        'product_description',
        'product_name',
        'category_name',
        'brand_name',
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
