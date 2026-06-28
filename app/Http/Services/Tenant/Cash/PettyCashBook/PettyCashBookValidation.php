<?php

namespace App\Http\Services\Tenant\Cash\PettyCashBook;

use App\Http\Concerns\HasSedeActiva;
use App\Models\Tenant\Cash\PettyCash;
use Exception;
use Illuminate\Support\Facades\Auth;

class PettyCashBookValidation
{
    use HasSedeActiva; // sedeActivaId() para el blindaje de sede

    private PettyCashBookRepository $s_repository;

    public function __construct(PettyCashBookRepository $_s_repository)
    {
        $this->s_repository =   $_s_repository;
    }

    public function validateOpenCash(array $data)
    {
        $petty_cash_id  =   $data['cash_available_id'];

        // CANDADO 1: la caja elegida no debe estar ya abierta.
        if ($this->s_repository->pettyCashIsOpen($petty_cash_id)) {
            throw new Exception("LA CAJA YA FUE APERTURADA!!!");
        }

        // CANDADO 2: vendedor único GLOBAL (sin filtro de sede). Un usuario con caja
        // abierta no abre otra en NINGUNA sede.
        if ($this->s_repository->getCashBookUser(Auth::id())) {
            throw new Exception("Ya tenés una caja abierta. Cerrala antes de abrir otra.");
        }

        // BLINDAJE: la caja debe ser de la sede activa (no se confía en el id del cliente).
        $caja = PettyCash::find($petty_cash_id);
        if (! $caja || (int) $caja->sede_id !== (int) $this->sedeActivaId()) {
            throw new Exception("Esa caja no pertenece a tu sede activa.");
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
