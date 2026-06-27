<?php

namespace App\Http\Services\Tenant\Cash\PettyCash;

class CashDto
{
    // Alta: la caja nace en su sede, CERRADA. (El combo se sincroniza aparte en el service.)
    public function getDtoStore(array $datos)
    {
        return [
            'sede_id' => $datos['sede_id'],
            'name'    => mb_strtoupper($datos['name'], 'UTF-8'),
            'type'    => 'CAJA',
            'status'  => 'CERRADO',
        ];
    }

    // Edición: solo el nombre (sede INMUTABLE; el combo se sincroniza aparte).
    public function getDtoUpdate(array $datos)
    {
        return [
            'name' => mb_strtoupper($datos['name'], 'UTF-8'),
        ];
    }
}
