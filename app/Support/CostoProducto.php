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
     * - admin           -> true SIEMPRE (excepción; flag Sí o No).
     * - cualquier otro  -> depende del flag: VVC='1' -> ve; VVC='0' -> NO ve.
     *
     * O sea: admin es la única excepción; el flag gobierna a TODOS los demás (ventas, técnico,
     * cualquier rol). Sin sesión -> fail-closed (depende del flag, no del rol).
     */
    public static function puedeVer(): bool
    {
        if (Auth::user()?->hasRole('admin')) {
            return true;
        }

        return Configuration::where('symbol', 'VVC')->value('property') === '1';
    }
}
