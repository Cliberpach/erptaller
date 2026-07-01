<?php

namespace App\Http\Middleware;

use App\Http\Concerns\HasSedeActiva;
use App\Models\Company;
use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetSedeActiva
{
    use HasSedeActiva;

    public function handle(Request $request, Closure $next): Response
    {
        // Defensivo: solo aplica en contexto tenant autenticado con la tabla disponible.
        // (No rompe landlord ni una BD aún sin migrar.)
        if (! Auth::check() || ! Schema::hasTable('sedes')) {
            return $next($request);
        }

        // Autosana la sesión (default / principal / primera válida) y comparte con las vistas.
        $this->sedeActivaId();

        View::share('sedeActiva', $this->sedeActiva());
        View::share('sedesDisponibles', $this->sedesDisponibles());
        View::share('cajaAbierta', $this->tieneCajaAbierta());   // CANDADO 3 (UI selector)

        // Plan REAL de la empresa (misma fuente que PlanMiddleware: companies.plan -> plans).
        // El header lo muestra; antes usaba plans->first() y mostraba siempre el primero (BÁSICO).
        View::share('planActual', Plan::find(Company::first()?->plan)?->description);

        return $next($request);
    }
}
