<?php

namespace App\Models\Tenant\Maintenance\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';
    protected $connection = 'tenant';
    protected $guarded = [''];
}
