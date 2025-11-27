<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Module;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        $databaseConnection = (parse_url(config("app.url"), PHP_URL_HOST) === request()->getHost()) ? 'landlord' : 'tenant';
        config(['database.default' => $databaseConnection]);

        /*$base = ($databaseConnection === 'landlord') ? 'landlord' : 'tenant';
        $modules = Module::where('show', $base)
            ->with(['children' => function ($query) use ($base) {
                $query->where('show', $base);
            }, 'children.grandchildren' => function ($query) use ($base) {
                $query->where('show', $base);
            }])
            ->get();

        // Compartir variables globales con las vistas
        View::share('base', $base . '.');
        View::share('modules', $modules);*/


    }

}
