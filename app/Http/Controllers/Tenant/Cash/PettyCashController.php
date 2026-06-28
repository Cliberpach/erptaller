<?php

namespace App\Http\Controllers\Tenant\Cash;

use App\Http\Concerns\HasSedeActiva;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Cash\Cash\CashStoreRequest;
use App\Http\Requests\Tenant\Cash\Cash\CashUpdateRequest;
use App\Http\Services\Tenant\Cash\PettyCash\CashManager;
use App\Models\Tenant\Cash\PettyCash as PettyCashSede;
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
    use HasSedeActiva;

    private CashManager $s_cash;

    public function __construct()
    {
        $this->s_cash   =   new CashManager();
    }

    public function index()
    {
        // Caja = sede + nombre. Cualquier vendedor de la sede abre una caja libre de su sede.
        $sedes = $this->sedesDisponibles();

        return view('cash.petty-cash.index', [
            'sedes'        => $sedes,
            'sedeActivaId' => $this->sedeActivaId(),
        ]);
    }


    public function getListCash(Request $request)
    {
        // Cajas de las sedes disponibles del usuario (admin: todas; vendedor: las suyas). Blindaje.
        $cashes = DB::connection('tenant')
            ->table('petty_cashes as c')
            ->leftJoin('sedes as s', 's.id', '=', 'c.sede_id')
            ->whereIn('c.sede_id', $this->sedesDisponibles()->pluck('id'))
            ->where('c.status', '<>', 'ANULADO')
            ->select(
                'c.id',
                'c.name',
                'c.status',
                'c.sede_id',
                's.nombre as sede_nombre',
                'c.created_at'
            );

        return DataTables::of($cashes)->make(true);
    }

    public function getCash(int $id)
    {
        $cash = PettyCashSede::find($id);

        if (! $cash) {
            return response()->json(['success' => false, 'message' => 'La caja no existe.'], 404);
        }
        if (! $this->puedeAccederSede($cash->sede_id)) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso a esa caja.'], 403);
        }

        return response()->json(['success' => true, 'data' => [
            'id'      => $cash->id,
            'name'    => $cash->name,
            'sede_id' => $cash->sede_id,
        ]]);
    }

    public function store(CashStoreRequest $request)
    {
        $sedeId = (int) $request->get('sede_id');

        // BLINDAJE: la sede del request debe ser del usuario (no se confía en el cliente).
        if (! $this->puedeAccederSede($sedeId)) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso a esa sede.'], 403);
        }

        DB::beginTransaction();
        try {
            $this->s_cash->store($request->toArray());

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
            $cash = PettyCashSede::find($id);

            if (! $cash) {
                return response()->json(['success' => false, 'message' => 'La caja no existe.'], 404);
            }
            if (! $this->puedeAccederSede($cash->sede_id)) {
                return response()->json(['success' => false, 'message' => 'No tiene acceso a esa caja.'], 403);
            }

            // sede_id es INMUTABLE (el service solo actualiza el nombre).
            $this->s_cash->update($request->toArray(), $id);

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
            $cash = PettyCashSede::find($id);

            if (! $cash) {
                return response()->json(['success' => false, 'message' => 'La caja no existe.'], 404);
            }
            if (! $this->puedeAccederSede($cash->sede_id)) {
                return response()->json(['success' => false, 'message' => 'No tiene acceso a esa caja.'], 403);
            }

            // GUARD (dinero): no anular una caja con apertura abierta. Hay que cerrarla primero.
            $abierta = $cash->status === 'ABIERTO'
                || DB::table('petty_cash_books')->where('petty_cash_id', $id)->where('status', 'ABIERTO')->exists();
            if ($abierta) {
                return response()->json(['success' => false, 'message' => 'No se puede anular una caja con apertura abierta. Ciérrela primero.']);
            }

            $this->s_cash->destroy($id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CAJA ANULADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /**
     * Seguridad: la caja debe pertenecer a una sede disponible del usuario.
     * Reusa HasSedeActiva (admin → todas; vendedor → sus sede_user). No duplica lógica.
     */
    private function puedeAccederSede($sedeId): bool
    {
        return $this->sedesDisponibles()->contains('id', (int) $sedeId);
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
