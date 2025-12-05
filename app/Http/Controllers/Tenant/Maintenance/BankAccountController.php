<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Tenant\Maintenance\BankAccount\BankAccountStoreRequest;
use App\Http\Requests\Tenant\Maintenance\BankAccount\BankAccountUpdateRequest;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Tenant\Maintenance\BankAccount\BankAccount;
use App\Models\Tenant\Sale\PaymentMethodAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class BankAccountController extends Controller
{
    public function index()
    {
        $banks  =   UtilController::getBanks();
        return view('maintenance.bank_accounts.index', compact('banks'));
    }

    public function getBankAccounts(Request $request)
    {
        $cuentas   =   DB::table('bank_accounts as c')
            ->select(
                'c.id',
                'c.holder',         // titular
                'c.bank_id',        // banco_id
                'c.bank_name',      // banco_nombre
                'c.account_number', // nro_cuenta
                'c.cci',            // cci
                'c.phone',          // celular
                'c.currency'        // moneda
            )
            ->where('status', '<>', 'ANULADO'); // estado

        return DataTables::of($cuentas)->make(true);
    }

    /*
array:7 [ // app\Http\Controllers\Tenant\Maintenance\BankAccountController.php:55
  "_token" => "tl053q5vHVqcyrnHwQtTbM7S039yQjOwcAkblfdS"
  "holder" => "ALVA LUJAN LUIS DANIEL"
  "currency" => "SOLES"
  "account_number" => "66233343"
  "cci" => "423423443"
  "phone" => "918817134"
  "bank_id" => "42"
]
*/
    public function store(BankAccountStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $data                   =   $request->validated();
            $data['holder']        =   mb_strtoupper($data['holder'], 'UTF-8');
            $data['currency']         =   mb_strtoupper($data['currency'], 'UTF-8');

            $bank = GeneralTableDetail::findOrFail($request->get('bank_id'));
            $data['bank_name']      =   $bank->name;
            $data['bank_abbreviation']      =   $bank->symbol;

            $data['editable']       =   1;

            BankAccount::create($data);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CUENTA REGISTRADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /*
array:5 [
  "titular" => "ALVA LUJAN LUIS DANIEL editado"
  "banco_id" => "5"
  "moneda" => "SOLES"
  "nro_cuenta" => "6554244215"
  "cci" => "445261223"
]
*/
    public function update(BankAccountUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data                   =   $request->validated();
            $data['holder']        =   mb_strtoupper($data['holder'], 'UTF-8');
            $data['currency']         =   mb_strtoupper($data['currency'], 'UTF-8');
            $bank = GeneralTableDetail::findOrFail($request->get('bank_id'));
            $data['bank_name']      =   $bank->name;
            $data['bank_abbreviation']      =   $bank->symbol;
            $data['editable']       = 1;

            $bank_account              =   BankAccount::findOrFail($id);
            $bank_account->update($data);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CUENTA BANCARIA ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $bank_account                    =   BankAccount::findOrFail($id);
            $bank_account->status            =   'ANULADO';
            $bank_account->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CUENTA ELIMINADA']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getListBankAccounts(Request $request)
    {
        try {
            $payment_method_id  =   $request->get('payment_method_id');
            $bank_accounts  =   PaymentMethodAccount::from('payment_method_accounts as pma')
                                ->join('bank_accounts as ba','ba.id','pma.bank_account_id')
                                ->select(
                                    'pma.bank_account_id as id',
                                    DB::raw('CONCAT(ba.bank_abbreviation,":",ba.account_number,"-",ba.phone) as text')
                                )
                                ->where('payment_method_id', $payment_method_id)->get();

            return response()->json(['success' => true, 'message' => 'CUENTAS OBTENIDAS', 'bank_accounts' => $bank_accounts]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
