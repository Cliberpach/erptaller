<?php

namespace App\Http\Controllers\Tenant\Cash;

use App\Exports\Tenant\Cash\ExitMoney\ExitMoneyExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Cash\ExitMoney\ExitMoneyStoreRequest;
use App\Models\Company;
use App\Models\ExitMoney;
use App\Models\ExitMoneyDetail;
use App\Models\ProofPayment;
use App\Models\Supplier;
use App\Models\Tenant\Cash\PettyCashBook;
use App\Models\Tenant\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ExitMoneyController extends Controller
{
    public function index()
    {
        return view('cash.exit-money.index', [
            // Fechas por defecto = mes en curso, filtrando por em.date (fecha de emisión
            // del egreso, no created_at/tipeo). El datatable carga al entrar.
            'from_today' => now()->startOfMonth()->toDateString(),
            'to_today'   => now()->toDateString(),
        ]);
    }

    public function queryAll(Request $request)
    {
        $supplier_id    =   $request->get('supplier');
        $reason         =   $request->get('reason');
        $from_date      =   $request->get('from_date');
        $to_date        =   $request->get('to_date');

        $query = DB::connection('tenant')
            ->table('exit_money as em')
            ->join('suppliers as s', 's.id', '=', 'em.supplier_id')
            ->select(
                'em.id',
                'em.date',
                'em.reason',
                's.name as supplier_name',
                'em.number',
                'em.total'
            )
            ->where('em.status', 1);

        if ($supplier_id) {
            $query->where('em.supplier_id', $supplier_id);
        }
        if ($reason) {
            $query->where('em.reason', $reason);
        }
        if ($from_date) {
            $query->whereDate('em.date', '>=', $from_date);
        }
        if ($to_date) {
            $query->whereDate('em.date', '<=', $to_date);
        }

        // Orden cronológico (más viejo arriba). Vale para el Excel; el datatable
        // fija su propio orden inicial en JS.
        $query->orderBy('em.date', 'asc');

        return $query;
    }

    public function getExitMoneys(Request $request)
    {
        return DataTables::of($this->queryAll($request))->toJson();
    }

    public function excelExitMoneys(Request $request)
    {
        $company    =   Company::find(1);

        // Mismo query que el datatable (queryAll): el Excel exporta exactamente lo
        // que se ve filtrado (proveedor, razón, rango sobre em.date), no todo.
        $data       =   $this->queryAll($request)->get();

        $supplier   =   null;
        if ($request->get('supplier')) {
            $supplier   =   Supplier::findOrFail($request->get('supplier'));
        }
        $request->merge(['supplier' => $supplier]);

        return Excel::download(
            new ExitMoneyExport($data, $request, $company),
            'egresos_' . Carbon::now()->format('Y-m-d') . '.xlsx'
        );
    }


    public function create()
    {
        $suppliers = Supplier::all();
        $proof_payments = ProofPayment::all();
        $date = now()->format('Y-m-d');
        $payment_methods    =   PaymentMethod::where('estado', 'ACTIVO')->get();

        return view('cash.exit-money.create', compact(
            'suppliers',
            'proof_payments',
            'date',
            'payment_methods'
        ));
    }

    public function store(ExitMoneyStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $petty_cash = DB::table('petty_cash_books')
                ->where('user_id', Auth::id())
                ->where('status', 'ABIERTO')
                ->orderByDesc('id')
                ->first();

            if (!$petty_cash) {
                throw new Exception("NO FORMAS PARTE DE UNA CAJA ABIERTA");
            }

            $payment_method =   PaymentMethod::findOrFail($request->get('payment_method_id'));

            // PASO 4: cuenta de ORIGEN + blindaje cuenta<->método (espejo del cobro; no se
            // confía en el cliente). Efectivo (método sin cuentas) -> bank_account_id NULL.
            $bankAccountId  =   $request->get('bank_account_id') ?: null;
            $tieneCuentas   =   DB::table('payment_method_accounts')->where('payment_method_id', $payment_method->id)->exists();
            if ($bankAccountId) {
                $pertenece = DB::table('payment_method_accounts')
                    ->where('payment_method_id', $payment_method->id)
                    ->where('bank_account_id', $bankAccountId)
                    ->exists();
                if (! $pertenece) {
                    throw new Exception("La cuenta seleccionada no pertenece al método de pago.");
                }
            } elseif ($tieneCuentas) {
                throw new Exception("Debe seleccionar una cuenta de origen para el método " . $payment_method->description . ".");
            }

            $exit_money = new ExitMoney();
            $cajaAbierta = PettyCashBook::where('status', 'ABIERTO')->first();
            $exit_money->proof_payment_id = $request->proof_payment;
            $exit_money->payment_method_id = $payment_method->id;
            $exit_money->bank_account_id = $bankAccountId;                      // NULL = efectivo
            $exit_money->operation_number = $request->get('operation_number') ?: null;
            $exit_money->number = $request->number;
            $exit_money->date = $request->date;
            $exit_money->reason = $request->reason;
            $exit_money->supplier_id = $request->supplier_id;
            $exit_money->user_id = Auth::id();
            $exit_money->petty_cash_book_id =   $petty_cash->id;
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

            Session::flash('message_success', 'EGRESO REGISTRADO CON ÉXITO');

            DB::commit();
            return response()->json(['success' => true, 'message' => 'EGRESO REGISTRADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function showPDF($id)
    {
        $exit_money = ExitMoney::findOrFail($id);
        $exit_money_detail = ExitMoneyDetail::where('exit_money_id', $exit_money->id)->get();
        $company = Company::first();


        $pdf = Pdf::loadView('cash.exit-money.pdf', compact('exit_money', 'exit_money_detail', 'company'));

        return $pdf->stream('egreso_' . $exit_money->id . '.pdf');
    }

    public function editExit($id)
    {
        $exit_money = ExitMoney::findOrFail($id);
        $exit_money_detail = ExitMoneyDetail::where('exit_money_id', $exit_money->id)->get();
        $suppliers = Supplier::all();
        $proof_payments = ProofPayment::all();
        return view('cash.exit-money.edit', compact('exit_money', 'exit_money_detail', 'suppliers', 'proof_payments'));
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


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $exit_money         = ExitMoney::findOrFail($id);
            $exit_money->status = false;
            $exit_money->save();

            $caja                   =   PettyCashBook::where('id', $exit_money->petty_cash_book_id)->first();
            $totalActualizado       =   DB::table('exit_money')->where('id', $exit_money->id)->value('total');
            $caja->closing_amount   =   $caja->closing_amount + $totalActualizado;

            $caja->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'EGRESO ELIMINADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
