<?php

namespace App\Models\Tenant\WorkShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $table = 'brandsv';

    protected $guarded = [''];

      public function models()
    {
        return $this->hasMany(ModelV::class, 'brand_id');
    }
}
