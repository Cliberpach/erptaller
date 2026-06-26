<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Throwable;

class RoleController extends Controller
{
    /** El rol con acceso total: nunca se le editan permisos (red de seguridad). */
    private const ROL_ADMIN = 'admin';

    public function index()
    {
        // Permisos agrupados por módulo (prefijo antes del primer punto) para el modal.
        $permisosAgrupados = Permission::orderBy('name')->get()
            ->groupBy(fn ($p) => explode('.', $p->name)[0]);

        return view('maintenance.roles.index', compact('permisosAgrupados'));
    }

    public function getRoles(Request $request)
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->select('id', 'name', 'created_at');

        return DataTables::of($roles)
            ->addColumn('es_admin', fn ($r) => $r->name === self::ROL_ADMIN ? 1 : 0)
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate(
            ['name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')]],
            [
                'name.required' => 'El nombre del rol es obligatorio.',
                'name.unique'   => 'Ya existe un rol con ese nombre.',
            ]
        );

        DB::beginTransaction();
        try {
            Role::create(['name' => mb_strtolower(trim($request->get('name')), 'UTF-8')]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'ROL REGISTRADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /** Devuelve los permisos que el rol tiene actualmente (para pre-marcar los checkboxes). */
    public function permisos($id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json(['success' => false, 'message' => 'El rol no existe.'], 404);
        }

        return response()->json([
            'success'  => true,
            'role'     => ['id' => $role->id, 'name' => $role->name, 'es_admin' => $role->name === self::ROL_ADMIN],
            'permisos' => $role->permissions->pluck('name'),
        ]);
    }

    public function syncPermisos(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);

            // Blindaje: el rol admin tiene acceso total y NO se le editan permisos.
            if ($role->name === self::ROL_ADMIN) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol ADMIN tiene acceso total y no se puede modificar.',
                ]);
            }

            $role->syncPermissions($request->get('permisos', []));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'PERMISOS ACTUALIZADOS']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
