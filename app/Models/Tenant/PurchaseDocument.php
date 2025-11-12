<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDocument extends Model
{
    use HasFactory;
    protected $table = 'purchase_documents';

    protected $guarded = [''];
}
