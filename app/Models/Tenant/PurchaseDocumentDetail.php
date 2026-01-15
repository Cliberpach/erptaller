<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDocumentDetail extends Model
{
    use HasFactory;
    protected $table = 'purchase_documents_detail';

    protected $fillable = [
        'purchase_document_id',
        'product_id',
        'category_id',
        'brand_id',
        'warehouse_id',
        'warehouse_name',
        'product_name',
        'category_name',
        'brand_name',
        'quantity',
        'purchase_price',
        'subtotal',
    ];
}
