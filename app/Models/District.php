<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $guarded = [''];
    protected $connection = 'landlord';

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
    ];

    public function province()
    {
        return $this->belongsTo('App\Province');
    }

    public function departament()
    {
        return $this->belongsTo('App\Departament');
    }
}
