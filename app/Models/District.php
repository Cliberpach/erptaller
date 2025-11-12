<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $guarded = [''];

    public $timestamps = false;

    public function province()
    {
        return $this->belongsTo('App\Province');
    }

    public function departament()
    {
        return $this->belongsTo('App\Departament');
    }
}
