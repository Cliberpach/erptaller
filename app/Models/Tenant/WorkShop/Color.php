<?php

namespace App\Models\Tenant\WorkShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;
    protected $table = 'colors';

    protected $guarded = [''];
}
