<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class PlanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,string $opcion): Response
    {
        $plan_actual    =   Plan::first();

        if($opcion === 'ventas' && $plan_actual->description === 'PLAN BÁSICO'){
            Session::flash('plan_md_error','DEBES SUBIR DE PLAN PARA PODER REALIZAR VENTAS!!!');
            return back();
        }
        if($opcion === 'inventario' && $plan_actual->description === 'PLAN BÁSICO'){
            Session::flash('plan_md_error','DEBES SUBIR DE PLAN PARA ACCEDER AL INVENTARIO!!!');
            return back();
        }
        if($opcion === 'compras' && $plan_actual->description === 'PLAN BÁSICO'){
            Session::flash('plan_md_error','DEBES SUBIR DE PLAN PARA ACCEDER A COMPRAS!!!');
            return back();
        }

        return $next($request);
    }
}
