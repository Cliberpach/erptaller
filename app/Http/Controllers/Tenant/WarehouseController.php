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
        return view('inventory.warehouses.index');
    }

    public function getWarehouses(Request $request)
    {
        // Solo los almacenes de la SEDE ACTIVA (la sede activa ya está limitada a las sedes
        // permitidas del usuario por HasSedeActiva — Etapa 2).
        $warehouses = DB::table('warehouses as w')
            ->leftJoin('sedes as s', 's.id', '=', 'w.sede_id')
            ->where('w.sede_id', $this->sedeActivaId())
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
        DB::beginTransaction();
        try {
            $warehouse               = new Warehouse();
            $warehouse->descripcion  = mb_strtoupper($request->get('descripcion'), 'UTF-8');
            $warehouse->sede_id      = $this->sedeActivaId();  // SIEMPRE la sede activa, NUNCA del request
            $warehouse->es_principal = false;                  // los creados a mano nunca son principal
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
