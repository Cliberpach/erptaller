<?php

namespace App\Http\Middleware\Tenant;

use App\Models\Tenant\Maintenance\Company\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = \Spatie\Multitenancy\Models\Tenant::current();

        if (! $tenant) {
            return $next($request);
        }

        $company = Cache::remember(
            "company_status_{$tenant->id}",
            now()->addHour(6),
            fn() => Company::findOrFail(1)
        );

        if ($company->block_account) {

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Tu cuenta ha sido temporalmente bloqueada. Por favor, contacta a nuestro equipo de soporte para reactivar tu servicio.',
                    'code' => 403,
                    'success' => false
                ], 403);
            }

            return response()->view('tenant.errors.company-status', [], 403);
        }

        return $next($request);
    }
}
