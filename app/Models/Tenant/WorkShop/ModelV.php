<?php

namespace App\Models\Tenant\WorkShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelV extends Model
{
    use HasFactory;
    protected $table = 'models';

    protected $guarded = [''];

    public function brand(){
        return $this->belongsTo(Brand::class,'brand_id');
    }
}
