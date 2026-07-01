<?php

namespace App\Support;

use App\Models\Tenant\Configuration;
use Illuminate\Support\Facades\Auth;

/**
 * Política del flag VEP ("EL USUARIO VENTAS PUEDE EDITAR EL PRECIO DE VENTA").
 *
 * ÚNICO punto de verdad para el front (Capa 2: readonly en el input de precio) y el back
 * (Capa 3: forzar el precio del producto si está bloqueado). No repetir esta lógica en las
 * pantallas/servicios: consumir siempre puedeEditar().
 *
 * El flag se identifica por SYMBOL ('VEP'), NO por id -> robusto ante el orden de las filas.
 */
class PrecioVenta
{
    /**
     * ¿El usuario actual puede editar el precio de venta?
     *
     * - admin                -> true (siempre).
     * - flag VEP en '1'      -> true (todos, incluido ventas).
     * - rol ventas + VEP '0' -> false (bloqueado).
     * - otros roles          -> true (el flag aplica solo a ventas).
     */
    public static function puedeEditar(): bool
    {
        $user = Auth::user();

        if (! $user || $user->hasRole('admin')) {
            return true;
        }

        $bloqueado = $user->hasRole('ventas')
            && Configuration::where('symbol', 'VEP')->value('property') === '0';

        return ! $bloqueado;
    }
}
