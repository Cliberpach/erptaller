<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $guarded = [''];
    protected $connection = 'landlord';

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
    ];

    public function departaments()
    {
        return $this->belongsTo('App\Departament');
    }

    public function districts()
    {
        return $this->hasMany('App\District');
    }
}
