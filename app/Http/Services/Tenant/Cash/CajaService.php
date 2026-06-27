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
 * El combo de vendedores nace VACÍO: se llena en el CRUD (Capa B). Una caja sin combo
 * no es operable; el CRUD lo deja explícito.
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
