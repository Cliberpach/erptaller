<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grifo\Herramientas\Cargos\CargoStoreRequest;
use App\Http\Requests\Grifo\Herramientas\Cargos\CargoUpdateRequest;
use App\Http\Requests\Tenant\Maintenance\Position\PositionStoreRequest;
use App\Http\Requests\Tenant\Maintenance\Position\PositionUpdateRequest;
use App\Models\Herramientas\Cargo;
use App\Models\Tenant\Maintenance\Position;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Throwable;

class PositionController extends Controller
{
    public function index()
    {

        return view('maintenance.positions.index');
    }

    public function getPositions(Request $request)
    {

        $cargos =   DB::table('positions as p')
                    ->where('status', 'ACTIVO')
                    ->select(
                        'id',
                        'name',
                        'created_at',
                        'updated_at'
                    );

        return DataTables::of($cargos)
            ->make(true);
    }

    public function store(PositionStoreRequest $request)
    {

        DB::beginTransaction();
        try {

            $cargo              =   new Position();
            $cargo->name        =   mb_strtoupper($request->get('name'), 'UTF-8');
            $cargo->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CARGO REGISTRADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function update(PositionUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $cargo              =   Position::findOrFail($id);
            $cargo->name        =   mb_strtoupper($request->get('name_edit'), 'UTF-8');
            $cargo->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CARGO ACTUALIZADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $cargo                    =   Position::findOrFail($id);
            $cargo->status            =   'ANULADO';
            $cargo->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CARGO ELIMINADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
