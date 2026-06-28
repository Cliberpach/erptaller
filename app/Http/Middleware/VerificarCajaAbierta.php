<?php

namespace App\Http\Middleware;

use App\Http\Services\Tenant\Cash\PettyCashBook\PettyCashBookRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarCajaAbierta
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Cajas Capa C: exige la caja ABIERTA del USUARIO actual (no cualquiera).
        // getCashBookUser: user_id + status ABIERTO + final_date NULL.
        $cajaAbierta = (new PettyCashBookRepository())->getCashBookUser(Auth::id());

        if (!$cajaAbierta) {
            return redirect()->route('tenant.movimientos_caja.apertura_cierre')
                             ->with('error', 'No puedes acceder sin una caja abierta.');
        }

        return $next($request);
    }
}
