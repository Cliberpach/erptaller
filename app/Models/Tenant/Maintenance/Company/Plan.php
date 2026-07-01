<?php

namespace App\Models\Tenant\Maintenance\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';
    protected $connection = 'tenant';
    protected $guarded = [''];
}
