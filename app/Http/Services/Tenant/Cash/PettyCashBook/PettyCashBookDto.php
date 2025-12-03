<?php

namespace App\Http\Services\Tenant\Cash\PettyCashBook;

use App\Models\Tenant\Cash\PettyCash;
use Illuminate\Support\Facades\Auth;

class PettyCashBookDto
{
    public function getDtoStore(array $datos)
    {
        $petty_cash =   PettyCash::findOrFail($datos['cash_available_id']);

        $dto    =   [
            'petty_cash_id' =>  $datos['cash_available_id'],
            'shift_id'  =>  $datos['shift'],
            'user_id'   =>  Auth::user()->id,
            'initial_amount'    =>  $datos['initial_amount'],
            'initial_date'      =>  now(),
            'petty_cash_name'   =>  $petty_cash->name
        ];

        return $dto;
    }
}
