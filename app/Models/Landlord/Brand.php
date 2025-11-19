<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $table = 'brandsv';
    protected $connection = 'landlord';

    protected $guarded = [''];

      public function models()
    {
        return $this->hasMany(ModelV::class, 'brand_id');
    }
}
