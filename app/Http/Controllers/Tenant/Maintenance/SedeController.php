<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Concerns\HasSedeActiva;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Maintenance\Sede\SedeStoreRequest;
use App\Http\Requests\Tenant\Maintenance\Sede\SedeUpdateRequest;
use App\Models\Tenant\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;
use Throwable;

class SedeController extends Controller
{
    use HasSedeActiva;

    /**
     * Cambia la sede activa del usuario (selector del header).
     */
    public function cambiar($id)
    {
        if ($this->cambiarSede($id)) {
            Session::flash('message_success', 'SEDE ACTIVA CAMBIADA');
        } else {
            Session::flash('message_error', 'NO TIENE ACCESO A ESA SEDE');
        }

        return redirect()->back();
    }

    public function index()
    {
        return view('maintenance.sedes.index');
    }

    public function getSedes(Request $request)
    {
        $sedes = DB::table('sedes as s')
            ->select(
                's.id',
                's.numero',
                's.nombre',
                's.codigo',
                's.es_principal',
                's.direccion',
                's.telefono',
                's.ubigeo',
                's.status',
                's.created_at'
            );

        return DataTables::of($sedes)->make(true);
    }

    public function store(SedeStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $sede               = new Sede();
            $sede->numero       = (int) Sede::max('numero') + 1; // correlativo lógico (la principal = 1)
            $sede->nombre       = mb_strtoupper($request->get('nombre'), 'UTF-8');
            $sede->codigo       = mb_strtoupper($request->get('codigo'), 'UTF-8');
            $sede->es_principal = false; // las sedes adicionales NO son principales
            $sede->direccion    = $request->get('direccion');
            $sede->telefono     = $request->get('telefono');
            $sede->ubigeo       = $request->get('ubigeo');
            $sede->status       = 'ACTIVO';
            $sede->save();

            // Multi-Sede Etapa 3a: al crear una sede se siembra su almacén principal.
            \App\Models\Tenant\Warehouse::create([
                'sede_id'      => $sede->id,
                'descripcion'  => 'ALMACÉN PRINCIPAL',
                'es_principal' => true,
                'estado'       => 'ACTIVO',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'SEDE REGISTRADA']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function update(SedeUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $sede            = Sede::findOrFail($id);
            $sede->nombre    = mb_strtoupper($request->get('nombre_edit'), 'UTF-8');
            $sede->codigo    = mb_strtoupper($request->get('codigo_edit'), 'UTF-8');
            $sede->direccion = $request->get('direccion_edit');
            $sede->telefono  = $request->get('telefono_edit');
            $sede->ubigeo    = $request->get('ubigeo_edit');
            $sede->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'SEDE ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function toggleStatus($id)
    {
        DB::beginTransaction();
        try {

            $sede = Sede::findOrFail($id);

            // La sede principal no se puede desactivar.
            if ($sede->es_principal) {
                return response()->json([
                    'success' => false,
                    'message' => 'La sede principal no se puede desactivar.'
                ]);
            }

            $sede->status = $sede->status === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
            $sede->update();

            DB::commit();
            $msg = $sede->status === 'ACTIVO' ? 'SEDE ACTIVADA' : 'SEDE DESACTIVADA';
            return response()->json(['success' => true, 'message' => $msg, 'status' => $sede->status]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
