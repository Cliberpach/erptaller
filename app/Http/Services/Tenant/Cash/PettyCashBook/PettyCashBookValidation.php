<?php

namespace App\Http\Services\Tenant\Cash\PettyCashBook;

use App\Models\Tenant\Cash\PettyCash;
use Exception;

class PettyCashBookValidation
{
    private PettyCashBookRepository $s_repository;

    public function __construct(PettyCashBookRepository $_s_repository){
        $this->s_repository =   $_s_repository;
    }

    public function validateOpenCash(array $data){
        $petty_cash_id  =   $data['cash_available_id'];

        $petty_cash_open    =   $this->s_repository->pettyCashIsOpen($petty_cash_id);

        if($petty_cash_open){
            throw new Exception("LA CAJA YA FUE APERTURADA!!!");
        }
    }


}
