<?php

namespace App\Http\Controllers\Tenant\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\CustomerAccount\PayStoreRequest;
use App\Http\Services\Tenant\Accounts\CustomerAccount\CustomerAccountManager;
use App\Models\Tenant\Accounts\CustomerAccountDetail;
use App\Models\Tenant\PaymentMethod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Yajra\DataTables\DataTables;

class CustomerAccountController extends Controller
{
    private CustomerAccountManager $s_account;

    public function __construct()
    {
        $this->s_account    =   new CustomerAccountManager();
    }

    public function index()
    {
        $payment_methods    =   PaymentMethod::where('estado', 'ACTIVO')->get();

        return view('accounts.customer_accounts.index', compact('payment_methods'));
    }

    public function getCustomerAccounts(Request $request)
    {

        $customer_id =   $request->get('customer');
        $status     =   $request->get('status');

        $customer_accounts    =   DB::table('customer_accounts as ca')
            ->leftJoin('work_orders as sd', 'sd.id', 'ca.work_order_id')
            ->select(
                'ca.id',
                'ca.document_number',
                'sd.customer_name',
                'ca.document_date',
                'ca.amount',
                'ca.agreement',
                'ca.balance',
                'ca.status'
            )
            ->where('ca.status', '<>', 'ANULADO');

        if ($customer_id) {
            $customer_accounts->where('sd.customer_id', $customer_id);
        }
        if ($status) {
            $customer_accounts->where('ca.status', $status);
        }

        return DataTables::of($customer_accounts)->make(true);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->s_account->store($request->toArray());
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getCustomerAccount(int $id)
    {
        try {

            $cuenta =   DB::table('customer_accounts as ca')
                ->leftJoin('work_orders as wo', 'wo.id', 'ca.work_order_id')
                ->select(
                    'ca.id',
                    'ca.document_number',
                    'wo.customer_name',
                    'ca.amount',
                    'ca.balance',
                    'ca.status',
                    'ca.work_order_id'
                )
                ->where('ca.id', $id)
                ->first();

            if (!$cuenta) {
                throw new Exception("CUENTA CLIENTE NO EXISTE EN LA BD");
            }

            $detalle    =   CustomerAccountDetail::where('customer_account_id', $id)
                            ->orderByDesc('id')
                            ->get();

            return response()->json([
                'success' => true,
                'message' => 'CUENTA CLIENTE OBTENIDA',
                'data' => [
                    'cuenta' => $cuenta,
                    'detalle' => $detalle
                ]
            ]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /*
array:11 [ // app\Http\Controllers\Tenant\Accounts\CustomerAccountController.php:115
  "_token" => "OUiVLJK4B1xxUcncLt4KMQWShWUygPIGMkm5ZTu4"
  "pago" => "A CUENTA"
  "fecha" => "2025-12-05"
  "cantidad" => "10.00"
  "observacion" => "test"
  "efectivo_venta" => "0"
  "modo_pago" => "3"
  "cuenta" => "2"
  "nro_operacion" => "asd123"
  "importe_venta" => "10"
  "url_imagen" => null
]
*/
    public function storePago(PayStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $pay    =   $this->s_account->storePago($request->toArray());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'PAGO REGISTRADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(),'line'=>$th->getLine(),'file'=>$th->getFile()]);
        }
    }

    public function pdfOne(int $id){
        try {
            $pdf    =   $this->s_account->pdfOne($id);
            return $pdf;
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
