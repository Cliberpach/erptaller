<?php

namespace App\Http\Controllers\Tenant\Accounts;

use App\Exports\Tenant\Accounts\SupplierAccount\SupplierAccountExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Accounts\SupplierAccount\PayStoreRequest;
use App\Http\Services\Tenant\Accounts\SupplierAccount\SupplierAccountManager;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\Tenant\Accounts\SupplierAccount\SupplierAccountDetail;
use Illuminate\Http\Request;
use App\Models\Tenant\PaymentMethod;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SupplierAccountController extends Controller
{
    private SupplierAccountManager $s_account;

    public function __construct()
    {
        $this->s_account    =   new SupplierAccountManager();
    }

    public function index()
    {
        $payment_methods    =   PaymentMethod::where('estado', 'ACTIVO')->get();
        return view('accounts.supplier_accounts.index', compact('payment_methods'));
    }

    public function getAll(Request $request)
    {
        $accounts   =   $this->queryAll($request);

        return DataTables::of($accounts)->make(true);
    }

    public function queryAll(Request $request)
    {
        $supplier_id    =   $request->get('supplier');
        $status         =   $request->get('status');
        $start_date     =   $request->get('start_date');
        $end_date       =   $request->get('end_date');

        $accounts    =   DB::table('supplier_accounts as sa')
            ->leftJoin('purchase_documents as pd', 'sa.purchase_id', 'pd.id')
            ->select(
                'sa.id',
                'sa.document_number',
                DB::raw('pd.supplier_name as supplier_name'),
                'sa.document_date',
                'sa.amount',
                'sa.paid',
                'sa.balance',
                'sa.status',
                'sa.paid',
                'sa.creator_user_name'
            )
            ->where('sa.status', '<>', 'ANULADO');

        if ($supplier_id) {
            $accounts->where('pd.supplier_id', $supplier_id);
        }
        if ($status) {
            $accounts->where('sa.status', $status);
        }
        if ($start_date) {
            $accounts->whereDate('sa.created_at', '>=', $start_date);
        }
        if ($end_date) {
            $accounts->whereDate('sa.created_at', '<=', $end_date);
        }

        return $accounts;
    }

    public function getSupplierAccount(int $id)
    {
        try {

            $cuenta =   DB::table('supplier_accounts as sa')
                ->leftJoin('purchase_documents as pd', 'pd.id', 'sa.purchase_id')
                ->select(
                    'sa.id',
                    'sa.document_number',
                    'pd.supplier_name',
                    'sa.amount',
                    'sa.balance',
                    'sa.status',
                    'sa.purchase_id',
                    DB::raw('CONCAT("DC-",sa.purchase_id) as purchase_code'),
                    'sa.created_at',
                    'sa.updated_at',
                    'pd.supplier_id',
                    'sa.paid'
                )
                ->where('sa.id', $id)
                ->first();

            if (!$cuenta) {
                throw new Exception("CUENTA PROVEEDOR NO EXISTE EN LA BD");
            }

            $detalle    =   SupplierAccountDetail::from('supplier_accounts_details as sad')
                ->join('petty_cash_books as pcb', 'pcb.id', 'sad.petty_cash_book_id')
                ->where('sad.supplier_account_id', $id)
                ->orderByDesc('sad.id')
                ->select(
                    'sad.*',
                    'pcb.petty_cash_name'
                )
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'CUENTA PROVEEDOR OBTENIDA',
                'data' => [
                    'cuenta' => $cuenta,
                    'detalle' => $detalle
                ]
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
        }
    }


    /*
array:12 [ // app\Http\Controllers\Tenant\Accounts\SupplierAccountController.php:114
  "_token" => "0JvrFp9I9GVAcTU18qsT1MNbxkMdkqeFOXFyFOWW"
  "pago" => "A CUENTA"
  "metodo_pago" => "2"
  "fecha" => "2026-01-14"
  "cuenta" => "1"
  "cantidad" => "120.00"
  "efectivo_venta" => "0"
  "nro_operacion" => "aazzq112"
  "importe_venta" => "120"
  "observacion" => "test"
  "cuenta_proveedor_id" => "3"
  "img_pago" =>Illuminate\Http\UploadedFile {#2392}
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
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    /*
array:1 [▼ // app\Http\Controllers\Tenant\Accounts\SupplierAccountController.php:165
  "id" => "3"
]
*/
    public function pdfOne(Request $request)
    {
        $cuenta_id      =   $request->get('id');
        $data           =   $this->getSupplierAccount($cuenta_id);
        $data           =   $data->getData()->data;
        $company        =   Company::findOrFail(1);

        $pdf = Pdf::loadview('accounts.supplier_accounts.reports.pdf-one', [
            "company"               =>  $company,
            "data"                  =>  $data
        ])->setPaper('a4');

        return $pdf->stream('reporte_cuenta_proveedor' . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf');
    }

    public function pdfAll(Request $request)
    {

        $company     =   Company::findOrFail(1);

        $data        =   $this->queryAll($request)->get();

        $supplier    =   null;

        if ($request->get('supplier')) {
            $supplier    =   Supplier::findOrFail($request->get('supplier'));
        }


        $request->merge(['supplier' => $supplier]);

        $pdf = Pdf::loadview('accounts.supplier_accounts.reports.pdf-all', [
            'company'               =>  $company,
            'data'                  =>  $data,
            'filters'               =>  $request

        ])->setPaper('a4', 'landscape');


        return $pdf->stream('cuentas_proveedor_' . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf');
    }

     public function excelAll(Request $request)
    {

        $company        =   Company::find(1);

        $data           =   $this->queryAll($request)->get();

        $supplier        =   null;
        if ($request->get('supplier')) {
            $supplier    =   Supplier::findOrFail($request->get('supplier'));
        }

        $request->merge(['supplier' => $supplier]);

        return Excel::download(new SupplierAccountExport($data, $request, $company), 'cuentas_proveedor_' . Carbon::now()->format('Y-m-d') . '.xlsx');
    }
}
