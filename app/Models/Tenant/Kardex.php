<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
    use HasFactory;
    protected $table = 'kardex';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'brand_id',
        'category_id',
        'quantity',
        'price_sale',
        'amount',
        'type',
        'document',
        'product_name',
        'brand_name',
        'category_name',
        'sale_document_id',
        'note_income_id',
        'note_release_id',
        'purchase_document_id',
        'user_recorder_id',
        'user_recorder_name',
        'customer_id',
        'customer_name',
        'fecha_registro',
        'status',
    ];
}
