<?php

namespace App\Models\Tenant\WorkShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    use HasFactory;
    protected $table = 'model_years';

    protected $guarded = [''];

    public function brand(){
        return $this->belongsTo(Brand::class,'brand_id');
    }

    public function model(){
        return $this->belongsTo(ModelV::class,'model_id');
    }
}
