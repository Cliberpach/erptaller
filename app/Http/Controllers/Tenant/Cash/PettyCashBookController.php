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

        $shiftList = DB::select('select * from shifts');

        return view('cash.petty-cash-book.index', compact('shiftList'));
    }

    public function getCashBooks(Request $request)
    {
        $cashes = DB::connection('tenant')
            ->table('petty_cash_books as c')
            ->select(
                DB::raw("CONCAT('CM-', LPAD(c.id, 8, '0')) as code"),
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
            ->where('c.status', '<>', 'ANULADO')
            ->where('c.type','<>','FICTICIO');

        return DataTables::of($cashes)
            ->filterColumn('code', function ($query, $keyword) {
                $query->whereRaw("CONCAT('CM-', LPAD(c.id, 8, '0')) LIKE ?", ["%{$keyword}%"]);
            })
            ->toJson();
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

        try {
            $cashBook->status = $request->status;
            $PcashBook->status = $request->status;
            $cashBook->final_date = now();
            $cashBook->save();
            $PcashBook->save();

            return response()->json([
                'success' => true,
                'message' => 'El libro de caja ha sido cerrado exitosamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al cerrar el libro de caja.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getConsolidated(Request $request)
    {
        try {
            $consolidated   =   $this->s_pettycashbook->getConsolidated($request->get('id'));
            return response()->json(['success' => true, 'message' => 'CONSOLIDADO OBTENIDO', 'consolidated' => $consolidated]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function closePettyCash(Request $request)
    {
        try {
            $petty_cash_book   =   $this->s_pettycashbook->closePettyCash($request->toArray());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'CAJA CERRADA CON ÉXITO']);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
