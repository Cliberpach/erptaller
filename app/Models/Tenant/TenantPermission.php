<?php

namespace App\Models\Tenant;

use Spatie\Permission\Models\Permission;

class TenantPermission extends Permission
{
    protected $connection   =   'tenant';
    protected $table        =   'permissions';
}
