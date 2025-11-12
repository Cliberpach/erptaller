<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $guarded = [''];

    public $timestamps = false;

    public function departaments()
    {
        return $this->belongsTo('App\Departament');
    }

    public function districts()
    {
        return $this->hasMany('App\District');
    }
}
