<?php

namespace App\Http\Services\Tenant\PettyCash\PettyCashMovements;

class PettyCashMovementsManager
{
    private PettyCashMovementsService $s_petty_cash_movement;

     public function __construct()
    {
        $this->s_petty_cash_movement    =   new PettyCashMovementsService();
    }

    public function increaseClosingAmount(int $petty_cash_book_id,float $amount){
        $this->s_petty_cash_movement->increaseClosingAmount($petty_cash_book_id,$amount);
    }


}
