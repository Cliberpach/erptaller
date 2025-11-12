<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDocumentDetail extends Model
{
    use HasFactory;
    protected $table = 'purchase_documents_detail';

    protected $guarded = [''];
}
