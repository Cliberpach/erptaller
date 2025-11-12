<?php

namespace App\Http\Middleware;

use App\Models\PettyCashBook;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function Laravel\Prompts\alert;

class VerificarCajaAbierta
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Verifica si hay una caja abierta
        $cajaAbierta = PettyCashBook::where('status', 'open')->first();

        if (!$cajaAbierta) {
            return redirect()->route('tenant.cajas.apertura_cierre') 
                             ->with('error', 'No puedes acceder sin una caja abierta.');
        }

        return $next($request);
    }
}
