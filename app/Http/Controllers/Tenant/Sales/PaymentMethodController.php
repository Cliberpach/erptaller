<?php

namespace App\Http\Controllers\Tenant\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\PaymentMethod\PaymentMethodStoreRequest;
use App\Http\Requests\Tenant\PaymentMethod\PaymentMethodUpdateRequest;
use App\Models\Tenant\Maintenance\BankAccount\BankAccount;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Sale\PaymentMethodAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return view('sales.payment_method.index');
    }

    public function getPaymentMethods(Request $request)
    {

        $payment_methods    =   DB::table('payment_methods as p')
            ->select(
                'p.*'
            )
            ->where('p.estado', '!=', 'ANULADO')
            ->get();


        return DataTables::of($payment_methods)->make(true);
    }

    public function store(PaymentMethodStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $payment_method                 =   new PaymentMethod();
            $payment_method->description    =   $request->get('descripcion');
            $payment_method->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'MÉTODO DE PAGO REGISTRADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function update(PaymentMethodUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $payment_method                 =   PaymentMethod::find($id);
            $payment_method->description    =   $request->get('descripcion_edit');
            $payment_method->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'MÉTODO DE PAGO ACTUALIZADO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function assignAccountsCreate(int $id)
    {
        $tipo_pago          =   PaymentMethod::findOrFail($id);
        $cuentas            =   BankAccount::where('status', 'ACTIVO')->get();
        $cuentas_asignadas  =   PaymentMethodAccount::where('payment_method_id', $id)->get();
        return view('sales.payment_method.assign-accounts', compact('tipo_pago', 'cuentas', 'cuentas_asignadas'));
    }

    /*
array:2 [ // app\Http\Controllers\Tenant\Sales\PaymentMethodController.php:76
  "lstCuentasAsignadas" => "[1]"
  "tipo_pago_id" => "4"
]
    */
    public function assignAccountsStore(Request $request)
    {
        DB::beginTransaction();
        try {

            //======== BORRAR LAS CUENTAS ANTERIORES ========
            $tipo_pago_id   =   $request->get('tipo_pago_id');
            DB::delete('DELETE FROM payment_method_accounts WHERE payment_method_id = ?', [$tipo_pago_id]);

            $lstCuentasAsignadas    =   json_decode($request->get('lstCuentasAsignadas'));
            foreach ($lstCuentasAsignadas as $cuenta_asignada) {
                $cuenta_nueva               =   new PaymentMethodAccount();
                $cuenta_nueva->payment_method_id =   $tipo_pago_id;
                $cuenta_nueva->bank_account_id    =   $cuenta_asignada;
                $cuenta_nueva->save();
            }

            DB::commit();

            Session::flash('message_success','CUENTAS ASIGNADAS CON ÉXITO');
            return response()->json(['success' => true, 'message' => 'CUENTAS ASIGNADAS CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
