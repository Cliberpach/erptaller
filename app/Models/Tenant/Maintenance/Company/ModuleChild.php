<?php

namespace App\Models\Tenant\Maintenance\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleChild extends Model
{
    use HasFactory;

    protected $table = 'module_children';
    protected $connection = 'tenant';
    protected $guarded = [''];
}
