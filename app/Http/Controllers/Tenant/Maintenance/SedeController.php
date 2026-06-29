<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Concerns\HasSedeActiva;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Maintenance\Sede\SedeStoreRequest;
use App\Http\Requests\Tenant\Maintenance\Sede\SedeUpdateRequest;
use App\Http\Requests\Tenant\Maintenance\Sede\SerieUpdateRequest;
use App\Models\Department;
use App\Models\District;
use App\Models\Province;
use App\Models\Tenant\DocumentSerialization;
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
        } elseif ($this->tieneCajaAbierta()) {
            Session::flash('message_error', 'Cerrá tu caja para cambiar de sede.');
        } else {
            Session::flash('message_error', 'NO TIENE ACCESO A ESA SEDE');
        }

        return redirect()->back();
    }

    public function index()
    {
        // Ubigeo como 3 selects encadenados (mismo mecanismo que Registrar Cliente):
        // data precargada + cascada/filtrado en JS. Solo se persiste el distrito (ubigeo).
        return view('maintenance.sedes.index', [
            'departments' => Department::all(),
            'provinces'   => Province::all(),
            'districts'   => District::all(),
        ]);
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

            // Multi-Sede Etapa 5: la sede recibe sus series automáticamente (sufijo = numero).
            (new \App\Http\Services\Tenant\Maintenance\SerieService())->generarSeriesSede($sede);

            // Multi-Sede Etapa 4: la sede recibe su caja (solo sede + nombre; sin combo).
            (new \App\Http\Services\Tenant\Cash\CajaService())->crearCajaSede($sede);

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

    /**
     * Multi-Sede Etapa 5 (Capa B): series de UNA sede (para el modal "Series").
     * Blindaje: solo sedes disponibles del usuario.
     */
    public function getSeries($sedeId)
    {
        if (! $this->puedeAccederSede($sedeId)) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso a esa sede.'], 403);
        }

        $sede = Sede::find($sedeId);
        if (! $sede) {
            return response()->json(['success' => false, 'message' => 'La sede no existe.'], 404);
        }

        $series = DB::table('document_serializations')
            ->where('sede_id', $sedeId)
            ->orderBy('id')
            ->get(['id', 'serie', 'description as tipo', 'start_number', 'current_number']);

        return response()->json([
            'success'    => true,
            'sede_nombre' => $sede->nombre,
            'series'     => $series,
        ]);
    }

    /**
     * Edita serie + start_number de UNA serie. NO toca current_number (correlativo, lo maneja
     * la emisión en Capa C) ni sede_id/document_type_id. Blindaje por sede.
     */
    public function updateSerie(SerieUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $serie = DocumentSerialization::find($id);

            if (! $serie) {
                return response()->json(['success' => false, 'message' => 'La serie no existe.'], 404);
            }
            if (! $this->puedeAccederSede($serie->sede_id)) {
                return response()->json(['success' => false, 'message' => 'No tiene acceso a esa serie.'], 403);
            }

            $serie->serie        = mb_strtoupper($request->get('serie'), 'UTF-8');
            $serie->start_number = (int) $request->get('start_number');
            $serie->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'SERIE ACTUALIZADA']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /**
     * Seguridad: la sede debe pertenecer a una sede disponible del usuario.
     * Reusa HasSedeActiva (admin → todas; usuario normal → sus sede_user). No duplica lógica.
     */
    private function puedeAccederSede($sedeId): bool
    {
        return $this->sedesDisponibles()->contains('id', (int) $sedeId);
    }
}
