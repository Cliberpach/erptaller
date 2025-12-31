<?php

namespace App\Models\Tenant\Inventory\Kardex;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
    use HasFactory;

    protected $table = 'kardex';

    protected $fillable = [
        // Relaciones de documentos
        'sale_id',
        'purchase_id',
        'note_income_id',
        'note_release_id',
        'work_order_id',

        // Movimiento
        'type',
        'document_serie',
        'date',

        // Almacén
        'warehouse_id',
        'warehouse_name',

        // Producto
        'product_id',
        'category_id',
        'brand_id',
        'product_code',
        'product_unit',
        'product_description',
        'product_name',
        'category_name',
        'brand_name',

        // Valores
        'quantity',
        'sale_price',
        'purchase_price',
        'amount',

        // Cliente
        'customer_id',
        'customer_name',
        'customer_type_document_abbreviation',
        'customer_document_number',

        // Totales
        'total',
        'subtotal',
        'igv',

        // Auditoría
        'creator_user_id',
        'creator_user_name',
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

    }
}
