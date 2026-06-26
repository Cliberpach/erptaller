<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Concerns\HasSedeActiva;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Inventory\Warehouse\WarehouseStoreRequest;
use App\Http\Requests\Tenant\Inventory\Warehouse\WarehouseUpdateRequest;
use App\Models\Tenant\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Throwable;

class WarehouseController extends Controller
{
    use HasSedeActiva;

    public function index()
    {
        // Sedes para el selector del modal (admin: todas; normal: las suyas) + la activa para pre-seleccionar.
        return view('inventory.warehouses.index', [
            'sedes'        => $this->sedesDisponibles(),
            'sedeActivaId' => $this->sedeActivaId(),
        ]);
    }

    public function getWarehouses(Request $request)
    {
        // Almacenes de TODAS las sedes disponibles del usuario (admin: todas; normal: las de sede_user).
        // Respeta el blindaje: nunca muestra sedes ajenas.
        $warehouses = DB::table('warehouses as w')
            ->leftJoin('sedes as s', 's.id', '=', 'w.sede_id')
            ->whereIn('w.sede_id', $this->sedesDisponibles()->pluck('id'))
            ->select(
                'w.id',
                'w.descripcion',
                'w.estado',
                'w.es_principal',
                's.nombre as sede_nombre',
                'w.created_at'
            );

        return DataTables::of($warehouses)->make(true);
    }

    public function store(WarehouseStoreRequest $request)
    {
        $sedeId = (int) $request->get('sede_id');

        // BLINDAJE: la sede elegida en el dropdown DEBE ser del usuario (un request manipulado
        // podría mandar otro sede_id). No se confía en el cliente.
        if (! $this->puedeAccederSede($sedeId)) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso a esa sede.'], 403);
        }

        DB::beginTransaction();
        try {
            $warehouse               = new Warehouse();
            $warehouse->descripcion  = mb_strtoupper($request->get('descripcion'), 'UTF-8');
            $warehouse->sede_id      = $sedeId;  // validado contra sedesDisponibles()
            $warehouse->es_principal = false;    // los creados a mano nunca son principal
            $warehouse->estado       = 'ACTIVO';
            $warehouse->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'ALMACÉN REGISTRADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function edit($id)
    {
        $warehouse = Warehouse::find($id);

        if (! $warehouse) {
            return response()->json(['success' => false, 'message' => 'El almacén no existe.'], 404);
        }
        if (! $this->puedeAccederSede($warehouse->sede_id)) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso a ese almacén.'], 403);
        }

        return response()->json(['success' => true, 'warehouse' => $warehouse]);
    }

    public function update(WarehouseUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::find($id);

            if (! $warehouse) {
                return response()->json(['success' => false, 'message' => 'El almacén no existe.'], 404);
            }
            if (! $this->puedeAccederSede($warehouse->sede_id)) {
                return response()->json(['success' => false, 'message' => 'No tiene acceso a ese almacén.'], 403);
            }

            // INMUTABLES: sede_id (no se muda de sede → rompería su stock) y es_principal.
            // Solo se actualiza la descripción.
            $warehouse->descripcion = mb_strtoupper($request->get('descripcion_edit'), 'UTF-8');
            $warehouse->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'ALMACÉN ACTUALIZADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function toggleStatus($id)
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::find($id);

            if (! $warehouse) {
                return response()->json(['success' => false, 'message' => 'El almacén no existe.'], 404);
            }
            if (! $this->puedeAccederSede($warehouse->sede_id)) {
                return response()->json(['success' => false, 'message' => 'No tiene acceso a ese almacén.'], 403);
            }
            // El almacén principal de la sede NO se puede desactivar (lo usan los flujos de stock).
            if ($warehouse->es_principal) {
                return response()->json([
                    'success' => false,
                    'message' => 'El almacén principal no se puede desactivar.',
                ]);
            }

            $warehouse->estado = $warehouse->estado === 'ACTIVO' ? 'ANULADO' : 'ACTIVO';
            $warehouse->update();

            DB::commit();
            $msg = $warehouse->estado === 'ACTIVO' ? 'ALMACÉN ACTIVADO' : 'ALMACÉN DESACTIVADO';
            return response()->json(['success' => true, 'message' => $msg, 'estado' => $warehouse->estado]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /**
     * Seguridad: el almacén debe pertenecer a una sede disponible para el usuario.
     * Reusa HasSedeActiva (admin → todas; usuario normal → sus sede_user). No duplica lógica.
     */
    private function puedeAccederSede($sedeId): bool
    {
        return $this->sedesDisponibles()->contains('id', (int) $sedeId);
    }
}
