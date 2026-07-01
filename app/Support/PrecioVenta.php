<?php

namespace App\Support;

use App\Models\Product;
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

    /**
     * ENFORCEMENT server-side (Capa 3): fuerza el precio REAL del producto (products.sale_price)
     * cuando el usuario NO puede editar (ventas + flag=No). Ignora el sale_price que llegó del
     * front (por si sacaron el readonly por devtools o tampearon el JSON). Silencioso, no rechaza.
     *
     * - Si puedeEditar() -> devuelve los items intactos (admin o flag=Sí respetan el precio enviado).
     * - Recibe un array de items (stdClass) ya decodificado. Resuelve los precios en UN solo query.
     * - Reescribe sale_price. Si el item trae 'total' de línea (cotización/OT), lo recalcula
     *   (precio × cantidad) para que los TOTALES no queden con el precio tampereado. En venta los
     *   items no traen 'total' (el total se recalcula aguas abajo desde sale_price) -> solo precio.
     *
     * @param  array<int,\stdClass>  $items
     * @return array<int,\stdClass>
     */
    public static function forzarPrecios(array $items): array
    {
        if (self::puedeEditar() || empty($items)) {
            return $items;
        }

        $ids     = array_map(static fn ($it) => $it->id, $items);
        $precios = Product::whereIn('id', $ids)->pluck('sale_price', 'id'); // id => sale_price real

        foreach ($items as $it) {
            if (! $precios->has($it->id)) {
                continue; // producto inexistente: lo maneja la validación de stock, no forzamos
            }

            $real           = (float) $precios->get($it->id);
            $it->sale_price = $real;

            // Recalcular el total de línea solo si el item lo trae (cotización/OT).
            if (property_exists($it, 'total')) {
                $cantidad = property_exists($it, 'quantity') ? (float) $it->quantity
                    : (property_exists($it, 'cant') ? (float) $it->cant : null);

                if ($cantidad !== null) {
                    $it->total = round($real * $cantidad, 2);
                }
            }
        }

        return $items;
    }
}
