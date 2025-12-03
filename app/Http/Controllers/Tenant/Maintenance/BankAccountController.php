<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Tenant\Maintenance\BankAccount\BankAccountStoreRequest;
use App\Http\Requests\Tenant\Maintenance\BankAccount\BankAccountUpdateRequest;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Tenant\Maintenance\BankAccount\BankAccount;
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
            $data['bank_name']   =   GeneralTableDetail::findOrFail($request->get('bank_id'))->name;
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
            $data['bank_name']   =   GeneralTableDetail::findOrFail($request->get('bank_id'))->name;
            $data['editable']       =

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
}
