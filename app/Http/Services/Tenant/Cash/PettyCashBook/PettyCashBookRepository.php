<?php

namespace App\Http\Services\Tenant\Cash\PettyCashBook;

use App\Models\Tenant\Cash\PettyCash;
use App\Models\Tenant\Cash\PettyCashBook;
use Illuminate\Support\Facades\DB;

class PettyCashBookRepository
{
    public function insertPettyCashBook(array $dto): PettyCashBook
    {
        return PettyCashBook::create($dto);
    }

    public function updateCash(array $dto, int $id): PettyCash
    {
        $cash    =   PettyCash::findOrFail($id);
        $cash->update($dto);
        return $cash;
    }

    public function findCash(int $id): PettyCash
    {
        return PettyCash::findOrFail($id);
    }

    public function destroy(int $id): PettyCash
    {
        $cash    =   PettyCash::findOrFail($id);
        $cash->status   =   'ANULADO';
        $cash->save();
        return $cash;
    }

    public function getPettyCashBookInfo(int $id)
    {
        $cash_book  =   DB::table('petty_cash_books as pcb')
            ->join('users as u', 'u.id', 'pcb.user_id')
            ->select(
                'pcb.id',
                'pcb.petty_cash_name',
                'u.name as user_name',
                'pcb.initial_amount',
                'initial_date'
            )
            ->where('pcb.id', $id)
            ->first();
        return $cash_book;
    }

    public function searchCashAvailable($data)
    {
        $search = $data['search'] ?? null;

        $query  =   PettyCash::from('petty_cashes as pc')
            ->leftJoin('petty_cash_books as pcb', 'pc.id', 'pcb.petty_cash_id')
            ->where('pcb.status', 'CERRADO')
            ->orWhereNull('pcb.id')
            ->where('pc.status', 'CERRADO')
            ->distinct()
            ->when($search, function ($q) use ($search) {
                $q->where('pc.name', 'like', "%{$search}%");
            })
            ->select(
                'pc.id',
                'pc.name',
                'pc.status'
            )
            ->get();

        return $query;
    }

    public function pettyCashIsOpen(int $petty_cash_id)
    {
        $exists =   PettyCashBook::where('petty_cash_id', $petty_cash_id)->where('status', 'ABIERTO')->exists();
        return $exists;
    }

    public function getPettyCashBook(int $id)
    {
        return PettyCashBook::findOrFail($id);
    }

    public function getCashBookUser(int $user_id)
    {
        $cash_book  =   DB::table('petty_cash_books as pcb')
            ->join('petty_cashes as pc', 'pc.id', 'pcb.petty_cash_id')
            ->select(
                'pc.name',
                'pcb.id as petty_cash_book_id',
                'pc.id as petty_cash_id'
            )->where('pcb.status', 'ABIERTO')
            ->whereNull('pcb.final_date')
            ->where('pcb.user_id', $user_id)
            ->orderBy('pcb.id', 'ASC')
            ->first();

        return $cash_book;
    }
}
