<?php

namespace App\Http\Services\Tenant\Cash;

use App\Models\Tenant\Cash\PettyCash;
use App\Models\Tenant\Sede;

/**
 * Única fuente de verdad para crear la caja de una sede.
 *
 * La llaman SedeController@store (sedes nuevas) y el provisioning (sede principal),
 * igual que crearSeriesSede / almacén principal. NO se repite la lógica.
 *
 * La caja es solo sede + nombre. Nace CERRADA; cualquier vendedor de la sede la abre.
 */
class CajaService
{
    public function crearCajaSede(Sede $sede): PettyCash
    {
        return PettyCash::firstOrCreate(
            ['sede_id' => $sede->id, 'name' => 'CAJA ' . $sede->nombre],
            ['type' => 'CAJA', 'status' => 'CERRADO']
        );
    }
}
