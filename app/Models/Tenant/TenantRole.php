<?php

namespace App\Models\Tenant;

use Spatie\Permission\Models\Role;

class TenantRole extends Role
{
    protected $connection   =   'tenant';
    protected $table        =   'roles';
}
