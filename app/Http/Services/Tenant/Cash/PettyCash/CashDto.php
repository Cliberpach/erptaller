<?php

namespace App\Http\Services\Tenant\Cash\PettyCash;

class CashDto
{
    public function getDtoStore(array $datos)
    {
        $dto    =   [
            'name' => mb_strtoupper($datos['name'], 'UTF-8'),
        ];

        return $dto;
    }
}
