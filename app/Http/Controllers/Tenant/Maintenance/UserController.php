<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Maintenance\Users\UserStoreRequest;
use App\Http\Requests\Tenant\Maintenance\Users\UserUpdateRequest;
use App\Models\Tenant\Maintenance\Collaborator\Collaborator;
use App\Models\Tenant\TenantRole;
use App\Models\Tenant\User;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Throwable;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(): View
    {
        return view('maintenance.users.index');
    }

    public function getAll(Request $request)
    {

        $usuarios = DB::table('users as u')
            ->leftJoin('model_has_roles as mhr', 'mhr.model_id', '=', 'u.id')
            ->leftJoin('roles as r', 'r.id', '=', 'mhr.role_id')
            ->select(
                'u.id',
                'u.name as nombre',
                'u.email as correo',
                'u.created_at as fecha_registro',
                'r.name as rol_nombre',
                'u.status as estado',
            )
            ->where('u.status', 'ACTIVO')
            ->orderByDesc('u.id');

        return DataTables::of($usuarios)
            ->make(true);
    }

    public function create()
    {
        $collaborators  =   Collaborator::from('collaborators as c')
            ->where('c.status', 'ACTIVO')
            ->leftJoin('users as u', 'u.collaborator_id', 'c.id')
            ->whereNull('u.id')
            ->select('c.*')
            ->get();

        $roles          =   Role::all();

        return view('maintenance.users.create', compact(
            'collaborators',
            'roles'
        ));
    }

    public function edit($id)
    {
        $user           =   User::findOrFail($id);
        $role           =   DB::table('model_has_roles as mhr')
            ->join('roles as r', 'r.id', 'mhr.role_id')
            ->where('mhr.model_id', $id)
            ->select('r.id', 'r.name')
            ->first();

        $collaborators  =   Collaborator::from('collaborators as c')
            ->where('c.status', 'ACTIVO')
            ->leftJoin('users as u', 'u.collaborator_id', 'c.id')
            ->whereNull('u.id')
            ->orWhere('u.id', $id)
            ->select('c.*')
            ->get();

        $roles          =   Role::all();

        return view('maintenance.users.edit', compact(
            'collaborators',
            'roles',
            'user',
            'role'
        ));
    }

    public function store(UserStoreRequest $request)
    {

        DB::beginTransaction();
        try {

            //===== BUSCANDO COLABORADOR ======
            $colaborador                =   Collaborator::findOrFail($request->get('colaborador'));

            if (!$colaborador) {
                throw new Exception("COLABORADOR NO ENCONTRADO EN LA BASE DE DATOS");
            }

            //====== BUSCANDO ROL ========
            $rol                        =   TenantRole::findOrFail($request->get('rol'));

            $usuario                    =   new User();
            $usuario->collaborator_id   =   $request->get('colaborador');
            $usuario->name              =   mb_strtoupper($colaborador->full_name, 'UTF-8');
            $usuario->email             =   $request->get('correo');
            $usuario->password          =   Hash::make($request->get('password'));
            $usuario->password_visible  =   $request->get('password');
            $usuario->save();

            //======= ASIGNANDO ROL ========
            $usuario->assignRole($rol);

            Session::flash('message_success', 'USUARIO REGISTRADO CON ÉXITO.');
            DB::commit();
            return response()->json(['success' => true, 'message' => 'USUARIO REGISTRADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function update(UserUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            //===== BUSCANDO COLABORADOR ======
            $colaborador                    =   Collaborator::findOrFail($request->get('colaborador'));

            if (!$colaborador) {
                throw new Exception("COLABORADOR NO ENCONTRADO EN LA BASE DE DATOS");
            }

            //====== BUSCANDO ROL ========
            $rol                            =   TenantRole::findOrFail($request->get('rol'));

            $usuario                        =   User::findOrFail($id);
            $usuario->collaborator_id       =   $request->get('colaborador');
            $usuario->name                  =   mb_strtoupper($colaborador->full_name, 'UTF-8');
            $usuario->email                 =   $request->get('correo');
            $usuario->password              =   Hash::make($request->get('password'));
            $usuario->password_visible      =   $request->get('password');
            $usuario->update();

            //======== QUITAR ROL PREVIO ======
            $usuario->syncRoles([]);

            //======= ASIGNANDO nuevo ROL ========
            $usuario->assignRole($rol->name);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'USUARIO ACTUALIZADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $usuario                    =   User::findOrFail($id);
            $usuario->status            =   'ANULADO';
            $usuario->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'USUARIO ELIMINADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
