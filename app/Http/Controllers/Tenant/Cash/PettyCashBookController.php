<?php

namespace App\Http\Controllers\Tenant\Cash;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Cash\PettyCashBook\PettyCashBookStore;
use App\Http\Services\Tenant\Cash\PettyCashBook\PettyCashBookManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PettyCash;
use App\Models\PettyCashBook;
use Illuminate\Support\Facades\Session;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class PettyCashBookController extends Controller
{
    private PettyCashBookManager $s_pettycashbook;

    public function __construct()
    {
        $this->s_pettycashbook  =   new PettyCashBookManager();
    }

    //====== PDF MOVIMIENTO DE CAJA ======
    public function showPDF(Request $request)
    {
        try {
            return $this->s_pettycashbook->getPdfOne($request->toArray());
        } catch (Throwable $th) {
            Session::flash('message_error', $th->getMessage());
            return back();
        }
    }

    public function index()
    {

        $cashList = DB::select('select * from petty_cashes as c
                            where c.status = "created" or c.status="close" ');

        $shiftList = DB::select('select * from shifts');

        $cashBookList = DB::select('select cu.id as id
                            ,ca.id as petty_cash_id,ca.name as name_cash,
                            cu.initial_amount,cu.initial_date,
                            IF(cu.final_date is NULL,"-",cu.final_date) as final_date,
                            IF(cu.closing_amount is NULL,"-",cu.closing_amount) as closing_amount,
                            IF(cu.sale_day is NULL,"-",cu.sale_day) as sale_day,
                            ca.status as status_cajaprincipal,
                            cu.status as status_cajaunica
                            from petty_cashes as ca
                            inner join petty_cash_books as cu
                            on ca.id=cu.petty_cash_id');

        return view('cash.petty-cash-book.index', compact('cashList', 'shiftList', 'cashBookList'));
    }

    public function getCashBooks(Request $request)
    {
        $cashes = DB::connection('tenant')
            ->table('petty_cash_books as c')
            ->select(
                'c.id',
                'c.petty_cash_id',
                'c.status',
                'c.shift_id',
                'c.user_id',
                'c.initial_amount',
                'c.closing_amount',
                'c.initial_date',
                'c.final_date',
                'c.sale_day',
                'c.created_at',
                'c.updated_at',
                'c.petty_cash_name'
            )
            ->where('c.status', '<>', 'ANULADO');

        return DataTables::of($cashes)->toJson();
    }

    /*
array:3 [ // app\Http\Controllers\Tenant\Cash\PettyCashBookController.php:112
  "cash_available_id" => "4"
  "shift" => "1"
  "initial_amount" => "0"
]
*/
    public function openPettyCash(PettyCashBookStore $request)
    {
        DB::beginTransaction();
        try {
            $petty_cash =   $this->s_pettycashbook->openPettyCash($request->toArray());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'CAJA ABIERTA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function closeCashBook(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|string',
            'pettyid' => 'required|integer'
        ]);

        // Buscar el libro de caja por ID
        $PcashBook = PettyCash::find($request->pettyid);
        $cashBook = PettyCashBook::find($request->id);

        // Verificar si se encontraron ambos registros
        if (!$cashBook) {
            return response()->json([
                'success' => false,
                'message' => 'El libro de caja no se encontró.',
            ], 404); // Error 404: No encontrado
        }

        // Actualizar el estado a "close"
        try {
            $cashBook->status = $request->status;
            $PcashBook->status = $request->status;
            $cashBook->final_date = now(); // Actualizar la fecha de cierre a la fecha actual
            $cashBook->save();
            $PcashBook->save();

            // Devolver una respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'El libro de caja ha sido cerrado exitosamente.',
            ]);
        } catch (\Exception $e) {
            // Manejar cualquier error que ocurra durante la actualización
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al cerrar el libro de caja.',
                'error' => $e->getMessage(),
            ], 500); // Error 500: Error interno del servidor
        }
    }
}
