<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilController;
use App\Http\Requests\General\Herramientas\Colaboradores\ColaboradorStoreRequest;
use App\Http\Requests\Market\Herramientas\Colaboradores\ColaboradorUpdateRequest;
use App\Http\Requests\Tenant\Maintenance\Collaborator\CollaboratorStoreRequest;
use App\Http\Requests\Tenant\Maintenance\Collaborator\CollaboratorUpdateRequest;
use App\Models\Herramientas\Cargo;
use App\Models\Herramientas\Colaborador;
use App\Models\Herramientas\TipoDocumento;
use App\Models\Tenant\Maintenance\Collaborator\Collaborator;
use App\Models\Tenant\Maintenance\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Throwable;

class CollaboratorController extends Controller
{
    public function index()
    {
        return view('maintenance.collaborators.index');
    }

    public function getCollaborators(Request $request)
    {

        $colaboradores  =   DB::table('collaborators as co')
            ->join('positions as p', 'p.id', '=', 'co.position_id')
            ->select(
                'co.id',
                'co.full_name',
                'co.address',
                'co.phone',
                'co.document_number',
                'co.work_days',
                'co.rest_days',
                'co.monthly_salary',
                'p.name as position_name',
                'co.status',
                'co.document_type_id'
            )
            ->where('co.status', 'ACTIVO');

        return DataTables::of($colaboradores)
            ->make(true);
    }

    public function create()
    {
        $tipos_documento    =   UtilController::getIdentityDocuments();
        $cargos             =   UtilController::getPositions();

        return view('maintenance.collaborators.create', compact('tipos_documento', 'cargos'));
    }
    /*
array:10 [ // app\Http\Controllers\Tenant\Maintenance\CollaboratorController.php:68
  "_token" => "dAPIUyXAaWjvf8ytLrmFQa3b5NU9bt2CSRc6c2k7"
  "document_type" => "39"
  "document_number" => "75608753"
  "full_name" => "LUIS DANIEL ALVA LUJAN"
  "position_id_" => "3"
  "address" => "AV UNION 231"
  "phone" => "918817134"
  "work_days" => "30"
  "rest_days" => "10"
  "monthly_salary" => "9000"
]
*/
    public function store(CollaboratorStoreRequest $request)
    {

        DB::beginTransaction();
        try {

            $colaborador = new Collaborator();

            $colaborador->document_type_id = $request->get('document_type');
            $colaborador->document_number  = $request->get('document_number');

            $colaborador->full_name = mb_strtoupper($request->get('full_name'), 'UTF-8');

            $colaborador->position_id = $request->get('position');

            $colaborador->address = mb_strtoupper($request->get('address'), 'UTF-8');
            $colaborador->phone   = $request->get('phone');

            $colaborador->work_days = $request->get('work_days');
            $colaborador->rest_days = $request->get('rest_days');

            $colaborador->monthly_salary = $request->get('monthly_salary');
            $colaborador->daily_salary   = $request->get('monthly_salary') / 30;
            $colaborador->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'COLABORADOR REGISTRADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function edit($id)
    {
        $tipos_documento    =   UtilController::getIdentityDocuments();
        $cargos             =   UtilController::getPositions();
        $colaborador        =   Collaborator::findOrFail($id);

        return view('maintenance.collaborators.edit', compact('tipos_documento', 'colaborador', 'cargos'));
    }

    /*
array:11 [ // app\Http\Controllers\General\Herramientas\ColaboradorController.php:113
  "_token" => "dL9o15Dm6ajQSIg2ESJObz6Ij8tUx0B38DRAAu9g"
  "subsistema" => "SUPER"
  "tipo_documento" => "1"
  "nro_documento" => "75608753"
  "nombre" => "LUIS DANIEL ALVA LUJAN"
  "cargo" => "1"
  "direccion" => "Y DALE U"
  "telefono" => "974585471"
  "dias_trabajo" => "12.00"
  "dias_descanso" => "12.00"
  "pago_mensual" => "12222.00"
]
*/
    public function update(CollaboratorUpdateRequest $request, $id)
    {

        DB::beginTransaction();
        try {
            $colaborador                    =   Collaborator::find($id);

            $colaborador->document_type_id = $request->get('document_type');
            $colaborador->document_number  = $request->get('document_number');

            $colaborador->full_name = mb_strtoupper($request->get('full_name'), 'UTF-8');

            $colaborador->position_id = $request->get('position');

            $colaborador->address = mb_strtoupper($request->get('address'), 'UTF-8');
            $colaborador->phone   = $request->get('phone');

            $colaborador->work_days = $request->get('work_days');
            $colaborador->rest_days = $request->get('rest_days');

            $colaborador->monthly_salary = $request->get('monthly_salary');
            $colaborador->daily_salary   = $request->get('monthly_salary') / 30;
            $colaborador->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'COLABORADOR ACTUALIZADO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $colaborador                    =   Collaborator::find($id);
            $colaborador->status            =   'ANULADO';
            $colaborador->update();

            $user   =   User::where('collaborator_id',$id)->where('status','ACTIVO')->first();
            if($user){
                $user->status = 'ANULADO';
                $user->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'COLABORADOR ELIMINADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    //======== VALIDAR DNI ÚNICO EN LA BASE DE DATOS, COLABORADORES ========
    public function consultarDni($dni)
    {

        try {
            //======== VALIDANDO FORMATO DNI ========
            if (strlen($dni) !== 8) {
                throw new Exception("EL DNI DEBE CONTAR CON 8 DÍGITOS");
            }

            //======== VALIDAR DNI ÚNICO =========
            $existe =   DB::select(
                'select
                        c.id
                        from colaboradores as c
                        where c.nro_documento = ?
                        and c.estado = "ACTIVO"',
                [$dni]
            );

            if (count($existe) > 0) {
                throw new Exception('El dni ya existe en la tabla colaboradores');
            }

            //======== CONSULTANDO DNI EN API RENIEC ========
            $res_consulta_api   =   UtilController::apiDni($dni);
            $res                =   $res_consulta_api->getData();

            //======= EN CASO LA CONSULTA FUE EXITOSA =====
            if ($res->success) {
                return response()->json(['success' => true, 'data' => $res->data, 'message' => 'OPERACIÓN COMPLETADA']);
            } else {
                throw new Exception($res->message);
            }
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
