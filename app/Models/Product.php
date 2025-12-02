<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'description',
        'sale_price',
        'purchase_price',
        'stock',
        'stock_min',
        'code_factory',
        'code_bar',
        'img_route',
        'img_name'
    ];

    /**
     * Relación: Un producto pertenece a una categoría
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * Relación: Un producto pertenece a una marca
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
