<?php

namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PettyCashBookRequest;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Company;
use App\Models\ExitMoney;
use App\Models\PettyCash;
use App\Models\PettyCashBook;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class PettyCashBookController extends Controller
{
    //====== PDF MOVIMIENTO DE CAJA ======
    public function showPDF($id)
    {

        //====== OBTENER MOVIMIENTO =======
        $caja           =   PettyCashBook::findOrFail($id);
        $cajero         =   DB::select('select * from users as u where u.id = ?',[$caja->user_id])[0];

        $initial_date   =   \Carbon\Carbon::parse($caja->initial_date);
        $final_date     =   \Carbon\Carbon::parse($caja->final_date);

        $reservations   =   BookingDetail::whereBetween('created_at', [$initial_date, $final_date])
                            ->where('payment', '!=', '0')->get();
        $exit_money     = ExitMoney::whereBetween('created_at',[$initial_date,$final_date])->get();

        //======= OBTENER DATOS DE LA EMPRESA ========
        $company = Company::first();

        //========= OBTENER DOCUMENTOS DE VENTA ======
        $sale_documents     =   Sale::where('petty_cash_book_id',$id)->get();
        $payment_methods    =   PaymentMethod::where('estado','ACTIVO')->get();
        

        //====== VISTA PDF ==========
        $pdf = PDF::loadView('cash.ventaspdf', 
        compact('caja', 'reservations', 'company','exit_money','sale_documents','payment_methods','cajero'));

        //========= PAGINACIÓN 1/n =========
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font   = $dompdf->getFontMetrics()->get_font("helvetica", "bold");
        $dompdf->get_canvas()->page_text(530, 800, "{PAGE_NUM} / {PAGE_COUNT}", $font, 10, array(0, 0, 0));
        
        //======= VISUALIZAR PDF ==========
        return $pdf->stream('venta_' . $caja->id . '.pdf');
    }


    public function index(){

        $cashList= DB::select('select * from petty_cashes as c
                            where c.status = "created" or c.status="close" ');

        $shiftList = DB::select('select * from shifts');

        $cashBookList= DB::select('select cu.id as id
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
      
        return view('cash.cuadre',compact('cashList','shiftList','cashBookList'));
    }

    public function openPettyCash(PettyCashBookRequest $request){
        $request->validated();
        //registrando cuadre... apertura
         $idCaja=$request->input('idCaja');
         $idTurno=$request->input('idTurno');
         $idUsuario= Auth::user()->id;
         $cantidadInicial=$request->input('cantidadInicial');
         $fechaApertura= Carbon::now()->format('Y-m-d H:i:s');
         $nombreCaja= $request->input('nombreCaja');

         $cuadre = [
             'petty_cash_id' => $idCaja,
             'shift_id' => $idTurno,
             'user_id' => $idUsuario,
             'initial_amount' => $cantidadInicial,
             'initial_date' => $fechaApertura,
         ];

         $cuadre['id'] = DB::table('petty_cash_books')->insertGetId($cuadre); 

        //agregando el nombre de la caja para retornar
        $cuadre['name_cash']=$nombreCaja;
        $cuadre['final_date']="-";
        $cuadre['closing_amount']="-";
        $cuadre['sale_day']="-"; 
        $cuadre['status_cajaunica']="open";
        

         //actualizando el estado de la caja como abierta
         $consultaSQL = "UPDATE petty_cashes SET 
                        status = 'open' WHERE id = :cashId";
         DB::update($consultaSQL, ['cashId' => $idCaja]);

         return response()->json(['tipo' => 'success', 'data' => $cuadre]);
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
