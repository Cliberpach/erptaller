<?php

namespace App\Http\Services\Tenant\Cash\PettyCashBook;

use App\Models\Tenant\Cash\PettyCash;
use Exception;
use Illuminate\Support\Facades\Auth;

class PettyCashBookValidation
{
    private PettyCashBookRepository $s_repository;

    public function __construct(PettyCashBookRepository $_s_repository)
    {
        $this->s_repository =   $_s_repository;
    }

    public function validateOpenCash(array $data)
    {
        $petty_cash_id  =   $data['cash_available_id'];

        $petty_cash_open    =   $this->s_repository->pettyCashIsOpen($petty_cash_id);

        if ($petty_cash_open) {
            throw new Exception("LA CAJA YA FUE APERTURADA!!!");
        }
    }

    public function validationClosePettyCash(array $data)
    {
        $petty_cash_book_id  =   $data['id'];

        $petty_cash_book    =   $this->s_repository->getPettyCashBook(($petty_cash_book_id));
        if ($petty_cash_book->status !== 'ABIERTO') {
            throw new Exception("La caja seleccionada no está abierta. ESTADO: " . $petty_cash_book->status);
        }

        if (Auth::user()->id !== $petty_cash_book->user_id) {
            throw new Exception("No tienes permiso para cerrar esta caja. Solo el usuario asignado puede realizar esta acción.");
        }
    }
}
