<?php

namespace App\Tasks;

use Spatie\Multitenancy\Models\Tenant;
use Spatie\Permission\PermissionRegistrar;

class SwitchAuthTask implements \Spatie\Multitenancy\Tasks\SwitchTenantTask
{
    public function makeCurrent(Tenant $tenant): void
    {
        // Cuando entramos a un tenant
        config([
            'auth.providers.users.model' => \App\Models\Tenant\User::class,
            'permission.database_connection' => 'tenant',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function forgetCurrent(): void
    {
        // Cuando volvemos a landlord
        config([
            'auth.providers.users.model' => \App\Models\User::class,
            'permission.database_connection' => 'landlord',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
