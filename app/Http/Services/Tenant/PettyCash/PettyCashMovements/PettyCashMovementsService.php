<?php

namespace App\Http\Services\Tenant\PettyCash\PettyCashMovements;

use App\Models\PettyCashBook;

class PettyCashMovementsService
{

     public function __construct()
    {
    }

    public function increaseClosingAmount(int $petty_cash_book_id,float $amount){
        $petty_cash_book                    =   PettyCashBook::findOrFail($petty_cash_book_id);
        $petty_cash_book->closing_amount    +=  $amount;
        $petty_cash_book->sale_day          +=  $amount;
        $petty_cash_book->save();
    }


}
