<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $guarded = [''];

    public $timestamps = false;

    public function provinces()
    {
        return $this->hasMany('App\Province');
    }

    public function disctricts()
    {
        return $this->hasMany('App\District');
    }
}
