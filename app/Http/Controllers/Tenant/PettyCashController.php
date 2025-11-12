<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashRequest;
use App\Models\Company;
use App\Models\ExitMoney;
use App\Models\ExitMoneyDetail;
use Illuminate\Http\Request;
use App\Models\PettyCash;
use App\Models\PettyCashBook;
use App\Models\ProofPayment;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\DB;
use PDF;
class PettyCashController extends Controller
{

    

public function showPDF($id)
{
    // Obtener los datos del egreso, incluyendo detalles
    $exit_money = ExitMoney::findOrFail($id);
    $exit_money_detail = ExitMoneyDetail::where('exit_money_id', $exit_money->id)->get();
    $company = Company::first();


    // Pasar los datos a una vista para generar el PDF
    $pdf = PDF::loadView('exit-money.pdf', compact('exit_money', 'exit_money_detail','company'));

    // Devolver el PDF como descarga
    return $pdf->stream('egreso_' . $exit_money->id . '.pdf');
}


    public function index()
    {
        $cashList = DB::select('select * from petty_cashes');

        return view('cash.index', compact('cashList'));
    }

    public function store(CashRequest $request)
    {
        $request->validated();

        $cash = new PettyCash();
        $cash->name = $request->input('name');
        $cash->save();

        return response()->json(['type' => 'success', 'data' => $cash]);
    }

    public function update(CashRequest $request)
    {
        $request->validated();
        $id = $request->input('id');

        $cash = PettyCash::find($id);

        $cash->name = $request->input('name');
        $cash->save();

        return response()->json(['type' => 'success', 'data' => $cash]);
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $cash = PettyCash::find($id);
        $cash->delete();
        return response()->json(['type' => 'success', 'data' =>  $request->all()]);
    }

    public function charging()
    {
        $cajas = PettyCash::all();


        return response()->json(['cajas' => $cajas]);
    }


    public function pettyCash()
    {
        return view('caja.index');
    }

    public function  initialFinalBalancing()
    {
        //
    }

    public function exitMoney(Request $request)
    {
        $exit_money = ExitMoney::where('status', true);
        $from_today = now()->format('Y-m-d');
        $to_today = now()->format('Y-m-d');

        if ($request->from_date && $request->to_date) {
            $exit_money = $exit_money->where('date', '>=', $request->from_date)->where('date', '<=', $request->to_date);
            $from_today = $request->from_date;
            $to_today = $request->to_date;
        }

        $exit_money = $exit_money->get();

        return view('exit-money.index', compact('exit_money', 'from_today', 'to_today'));
    }

    public function createExit()
    {
        $suppliers = Supplier::all();
        $proof_payments = ProofPayment::all();
        $date = now()->format('Y-m-d');
        return view('exit-money.create', compact('suppliers', 'proof_payments', 'date'));
    }

    public function storeExit(Request $request)
    {
        $request->validate([
            'proof_payment' => 'required',
            'number' => 'required',
            'date' => 'required|date',
            'reason' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'description.*' => 'required|string',
            'total.*' => 'required|numeric|min:0',
        ], [
            'proof_payment.required' => 'El tipo de comprobante es obligatorio.',
            'number.required' => 'El número es obligatorio.',
            'date.required' => 'La fecha de emisión es obligatoria.',
            'date.date' => 'La fecha debe tener un formato válido.',
            'reason.required' => 'La razón es obligatoria.',
            'supplier_id.required' => 'Debe seleccionar un proveedor.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
            'description.*.required' => 'La descripción es obligatoria.',
            'description.*.string' => 'La descripción debe ser un texto.',
            'total.*.required' => 'El total es obligatorio.',
            'total.*.numeric' => 'El total debe ser un número válido.',
            'total.*.min' => 'El total debe ser un valor positivo.',
        ]);

        $petty_cash = DB::table('petty_cash_books')->where('user_id', Auth::id())->first();
        
        if ($petty_cash && $petty_cash->initial_amount != null ) {
            $exit_money = new ExitMoney();
            $cajaAbierta = PettyCashBook::where('status', 'open')->first();
            $exit_money->proof_payment_id = $request->proof_payment;
            $exit_money->payment_type = $request->payment_type;
            $exit_money->number = $request->number;
            $exit_money->date = $request->date;
            $exit_money->reason = $request->reason;
            $exit_money->supplier_id = $request->supplier_id;
            $exit_money->user_id = Auth::id();
            $exit_money->total = 0;
                      
            $exit_money->save();

            if ($cajaAbierta->closing_amount == null) {
                $cajaAbierta->closing_amount = $cajaAbierta->initial_amount;
            }

            for ($i = 0; $i < count($request->description); $i++) {
                $booking_detail = new ExitMoneyDetail();
                $booking_detail->exit_money_id = $exit_money->id;
                $booking_detail->description = $request->description[$i];
                $booking_detail->total = $request->total[$i];
                $booking_detail->save();

                DB::table('exit_money')->where('id', $exit_money->id)->increment('total', $request->total[$i]);
                
            }

            $exit_money_total_actualizado = DB::table('exit_money')->where('id', $exit_money->id)->value('total');
            
            $cajaAbierta->closing_amount = $cajaAbierta->closing_amount - $exit_money_total_actualizado;
            $cajaAbierta->save();


            return redirect()->route('tenant.cajas.egreso');
        } else {
            return back()->with('datos', 'Caja cerrada o no aperturada');
        }
    }

    public function supplierStore(Request $request)
    {
        $supplier = new Supplier();
        $supplier->identity_document = $request->identity_document;
        $supplier->document_number = $request->document_number;
        $supplier->name = $request->name;
        $supplier->address = $request->address;
        $supplier->save();

        return back()->with('datos', 'Proveedor registrado');
    }

    public function editExit($id)
    {
        $exit_money = ExitMoney::findOrFail($id);
        $exit_money_detail = ExitMoneyDetail::where('exit_money_id', $exit_money->id)->get();
        $suppliers = Supplier::all();
        $proof_payments = ProofPayment::all();
        return view('exit-money.edit', compact('exit_money', 'exit_money_detail', 'suppliers', 'proof_payments'));
    }

    public function updateExit(Request $request, $id)
{

    $request->validate([
        'proof_payment' => 'required',
        'number' => 'required',
        'date' => 'required|date',
        'supplier_id' => 'required|exists:suppliers,id',
        'description.*' => 'required|string',
        'total.*' => 'required|numeric|min:0',
    ], [
        'proof_payment.required' => 'El tipo de comprobante es obligatorio.',
        'number.required' => 'El número es obligatorio.',
        'date.required' => 'La fecha de emisión es obligatoria.',
        'date.date' => 'La fecha debe tener un formato válido.',
        'supplier_id.required' => 'Debe seleccionar un proveedor.',
        'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
        'description.*.required' => 'La descripción es obligatoria.',
        'description.*.string' => 'La descripción debe ser un texto.',
        'total.*.required' => 'El total es obligatorio.',
        'total.*.numeric' => 'El total debe ser un número válido.',
        'total.*.min' => 'El total debe ser un valor positivo.',
    ]);

    // Buscar el registro de egreso de dinero actual
    $exit_money = ExitMoney::findOrFail($id);
    $cajaAbierta = PettyCashBook::where('status', 'open')->first();

    // Guardar el total actual antes de actualizar
    $totalAnterior = $exit_money->total;

    // Actualizar los datos de ExitMoney
    $exit_money->proof_payment_id = $request->proof_payment;
    $exit_money->number = $request->number;
    $exit_money->date = $request->date;
    $exit_money->supplier_id = $request->supplier_id;
    $exit_money->total = 0; // Reseteamos a 0 porque vamos a recalcularlo con los nuevos detalles
    $exit_money->save();

    // Eliminar los detalles antiguos
    DB::table('exit_money_detail')->where('exit_money_id', $id)->delete();

    // Asegurarse de que la caja tiene un monto inicial de cierre
    if ($cajaAbierta->closing_amount == null) {
        $cajaAbierta->closing_amount = $cajaAbierta->initial_amount;
    }

    // Recalcular el total con los nuevos detalles y agregar detalles de la transacción
    for ($i = 0; $i < count($request->description); $i++) {
        $booking_detail = new ExitMoneyDetail();
        $booking_detail->exit_money_id = $exit_money->id;
        $booking_detail->description = $request->description[$i];
        $booking_detail->total = $request->total[$i];
        $booking_detail->save();

        // Incrementar el total del registro de exit_money
        DB::table('exit_money')->where('id', $exit_money->id)->increment('total', $request->total[$i]);
    }

    // Obtener el nuevo total actualizado
    $totalActualizado = DB::table('exit_money')->where('id', $exit_money->id)->value('total');

    // Calcular la diferencia entre el total anterior y el total actualizado
    $diferencia = $totalActualizado - $totalAnterior;

    // Actualizar el closing_amount solo con la diferencia
    $cajaAbierta->closing_amount -= $diferencia;
    $cajaAbierta->save();

    return redirect()->route('tenant.cajas.egreso');
}


    public function cancelExit($id)
    {
        $exit_money = ExitMoney::findOrFail($id);
        $cajaAbierta = PettyCashBook::where('status', 'open')->first();
        $totalActualizado = DB::table('exit_money')->where('id', $exit_money->id)->value('total');
        $cajaAbierta->closing_amount = $cajaAbierta->closing_amount + $totalActualizado;
        $exit_money->status = false;
        $exit_money->save();
        $cajaAbierta->save();

        return redirect()->route('tenant.cajas.egreso');
    }

    public function proofPaymentStore(Request $request)
    {
        $proof_payment = new ProofPayment();
        $proof_payment->description = $request->description;
        $proof_payment->save();

        return back()->with('datos', 'Comprobante de pago registrado');
    }
}
