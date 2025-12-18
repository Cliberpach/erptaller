<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $base = ($databaseConnection === 'landlord') ? 'landlord' : 'tenant';
        $modules = Module::where('show', $base)
            ->with(['children' => function ($query) use ($base) {
                $query->where('show', $base);
            }, 'children.grandchildren' => function ($query) use ($base) {
                $query->where('show', $base);
            }])
            ->get();

        // Compartir variables globales con las vistas
        View::share('base', $base . '.');
        View::share('modules', $modules);
        View::share('lst_search_modules', $this->getLstSearchModules($base));
    }

    public function getLstSearchModules($base)
    {
        $lst_modules    =   DB::select(
            'SELECT
                                mc.description AS name,
                                mc.route_name AS url,
                                (
                                    SELECT
                                        m.description as category
                                    FROM modules as m
                                    WHERE m.id = mc.module_id
                                ) AS category,
                                "fi fi-rr-file" AS icon
                            FROM module_children as mc
                            WHERE mc.route_name IS NOT NULL

                            UNION ALL

                            SELECT
                                mgc.description AS name,
                                mgc.route_name AS url,
                                (
                                    SELECT
                                        m2.description as category
                                    FROM module_children as mc2
                                    INNER JOIN modules as m2 ON m2.id = mc2.module_id
                                    WHERE mc2.id = mgc.module_child_id
                                ) AS category,
                                "fi fi-rr-file" AS icon
                            FROM module_grand_children  AS mgc
                            WHERE mgc.route_name IS NOT NULL');

        return $lst_modules;
    }
}
