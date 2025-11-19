<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelV extends Model
{
    use HasFactory;
    protected $table = 'models';
    protected $connection = 'landlord';

    protected $guarded = [''];

    public function brand(){
        return $this->belongsTo(Brand::class,'brand_id');
    }
}
