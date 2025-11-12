<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteIncomeDetail extends Model
{
    use HasFactory;
    protected $table = 'notes_income_detail';

    protected $fillable = [
        'note_income_id',
        'product_id',
        'brand_id',
        'category_id',
        'warehouse_id',
        'warehouse_name',
        'product_name',
        'brand_name',
        'category_name',
        'quantity'
    ];
}
