<?php

namespace App\Http\Controllers\Tenant\Cash;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Cash\Cash\CashStoreRequest;
use App\Http\Requests\Tenant\Cash\Cash\CashUpdateRequest;
use App\Http\Services\Tenant\Cash\PettyCash\CashManager;
use App\Models\Company;
use App\Models\ExitMoney;
use App\Models\ExitMoneyDetail;
use Illuminate\Http\Request;
use App\Models\PettyCash;
use App\Models\PettyCashBook;
use App\Models\ProofPayment;
use App\Models\Supplier;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use PDF;
use Throwable;

class PettyCashController extends Controller
{
    private CashManager $s_cash;

    public function __construct()
    {
        $this->s_cash   =   new CashManager();
    }

    public function index()
    {
        $cashList = DB::select('select * from petty_cashes');

        return view('cash.petty-cash.index', compact('cashList'));
    }


    public function getListCash(Request $request)
    {
        $cashes = DB::connection('tenant')
            ->table('petty_cashes as c')
            ->select(
                'c.id',
                'c.name',
                'c.created_at',
                'c.status'
            )
            ->where('c.status', '<>', 'ANULADO');

        return DataTables::of($cashes)->toJson();
    }

    public function getCash(int $id)
    {
        try {

            $cash  =   $this->s_cash->getCash($id);

            return response()->json(['success' => true, 'message' => 'CAJA OBTENIDA', 'data' => $cash]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function store(CashStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $cash   =   $this->s_cash->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CAJA REGISTRADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function update(CashUpdateRequest $request, int $id)
    {
        DB::beginTransaction();
        try {
            $cash   =   $this->s_cash->update($request->toArray(), $id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CAJA ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $cash  =   $this->s_cash->destroy($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'CAJA ELIMINADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
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

    public function proofPaymentStore(Request $request)
    {
        $proof_payment = new ProofPayment();
        $proof_payment->description = $request->description;
        $proof_payment->save();

        return back()->with('datos', 'Comprobante de pago registrado');
    }

    public function searchCashAvailable(Request $request)
    {
        try {

            $cashes =   $this->s_cash->searchCashAvailable($request->toArray());

            return response()->json([
                'success' => true,
                'data' => $cashes
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar cajas disponibles.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
