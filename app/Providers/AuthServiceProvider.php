<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Permisos P2: el admin pasa TODOS los @can / permission: sin importar permisos.
        // Devuelve null (no false) para no cortar la evaluación normal de los demás roles.
        Gate::before(fn ($user) => $user->hasRole('admin') ? true : null);
    }
}
