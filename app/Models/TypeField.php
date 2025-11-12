<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeField extends Model
{
    use HasFactory;
    protected $table = 'type_fields';
    protected $guarded = [''];
}
