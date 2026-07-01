<?php

namespace App\Support;

use App\Models\Tenant\Configuration;
use Illuminate\Support\Facades\Auth;

/**
 * Política del flag VVC ("EL USUARIO VENTAS PUEDE VER EL COSTO DEL PRODUCTO").
 *
 * Gemela de {@see PrecioVenta} (flag VEP). ÚNICO punto de verdad para el enforcement del costo
 * (ocultar el costo en pantallas + omitirlo del JSON) -> ESO es una tarea aparte por capas; acá
 * solo queda creado el helper, sin uso todavía.
 *
 * VVC REEMPLAZARÁ al permiso 'inventario.ver_costos' (no conviven) -> se resuelve en el enforcement.
 *
 * El flag se identifica por SYMBOL ('VVC'), NO por id -> robusto ante el orden de las filas.
 */
class CostoProducto
{
    /**
     * ¿El usuario actual puede ver el costo del producto?
     *
     * - admin                -> true (siempre).
     * - flag VVC en '1'      -> true (todos, incluido ventas).
     * - rol ventas + VVC '0' -> false (bloqueado).
     * - otros roles          -> true (el flag aplica solo a ventas).
     */
    public static function puedeVer(): bool
    {
        $user = Auth::user();

        if (! $user || $user->hasRole('admin')) {
            return true;
        }

        $bloqueado = $user->hasRole('ventas')
            && Configuration::where('symbol', 'VVC')->value('property') === '0';

        return ! $bloqueado;
    }
}
