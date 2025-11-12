<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $connection   = 'tenant';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

}
