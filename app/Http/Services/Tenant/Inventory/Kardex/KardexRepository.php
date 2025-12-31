<?php

namespace App\Http\Services\Tenant\Inventory\Kardex;

use App\Models\Tenant\Inventory\Kardex\Kardex;

class KardexRepository
{
    public function insertKardex(array $dto){
        Kardex::insert($dto);
    }
}
