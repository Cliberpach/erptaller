<?php

namespace App\Http\Controllers\Tenant\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\PaymentCondition\PaymentConditionStoreRequest;
use App\Http\Requests\Sale\PaymentCondition\PaymentConditionUpdateRequest;
use App\Models\Tenant\Sale\PaymentCondition\PaymentCondition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Throwable;

class PaymentConditionController extends Controller
{
    public function index()
    {
        return view('sales.payment_conditions.index');
    }

    public function getCondicionPago(Request $request)
    {

        $data   =   PaymentCondition::where('status', 'ACTIVO');

        return DataTables::of($data)
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i');
            })
            ->editColumn('updated_at', function ($row) {
                return Carbon::parse($row->updated_at)->format('d/m/Y H:i');
            })
            ->make(true);
    }

    public function create()
    {

        return view('sales.payment_conditions.create');
    }

    public function store(PaymentConditionStoreRequest $request)
    {

        DB::beginTransaction();

        try {
            $nombre_pago   =   null;

            if ($request->get('tipo') == 1) {
                $nombre_pago   =   'CONTADO';
            }

            if ($request->get('tipo') == 2) {
                $nombre_pago   =   'CREDITO';
            }

            $item               =   new PaymentCondition();
            $item->name         =   $nombre_pago;
            $item->type         =   $nombre_pago;
            $item->nro_days     =   $request->get('nro_dias');
            $item->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CONDICIÓN PAGO REGISTRADO!!']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function edit($id)
    {
        $condicion_pago =   PaymentCondition::find($id);
        return view('sales.payment_conditions.edit', compact('condicion_pago'));
    }

    public function update(PaymentConditionUpdateRequest $request, $id)
    {

        DB::beginTransaction();

        try {
            $nombre_tipo   =   null;

            if ($request->get('tipo') == 1) {
                $nombre_tipo   =   'CONTADO';
            }

            if ($request->get('tipo') == 2) {
                $nombre_tipo   =   'CREDITO';
            }

            $condicion_pago                 =   PaymentCondition::findOrFail($id);
            $condicion_pago->name           =   $nombre_tipo;
            $condicion_pago->type           =   $nombre_tipo;
            $condicion_pago->nro_days       =   $request->get('nro_dias');
            $condicion_pago->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CONDICIÓN PAGO ACTUALIZADO!!']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $condicion_pago                    =   PaymentCondition::findOrFail($id);
            $condicion_pago->status            =   'ANULADO';
            $condicion_pago->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CONDICIÓN DE PAGO ELIMINADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
