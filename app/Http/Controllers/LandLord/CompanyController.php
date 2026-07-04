<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyStoreRequest;
use App\Http\Requests\Landlord\Maintenance\Company\CompanyUpdateRequest;
use App\Http\Services\Landlord\Maintenance\Company\CompanyManager;
use App\Models\Department;
use App\Models\District;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Province;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Throwable;

class CompanyController extends Controller
{
    private CompanyManager $s_manager;

    public function __construct()
    {
        $this->middleware('auth');
        $this->s_manager = new CompanyManager();
    }

    public function index()
    {
        return view('company.landlord');
    }

    public function getCompanies(Request $request)
    {
        $companies = DB::table('companies as e')
            ->join('tenants as t', 'e.tenant_id', 't.id')
            ->join('plans as p', 'p.id', 'e.plan')
            ->leftJoin('company_invoice as ci', 'ci.company_id', 'e.id')
            ->select(
                'e.id',
                'e.ruc',
                'e.business_name',
                'e.created_at',
                't.id as tenant_id',
                't.domain',
                'p.description as plan_name',
                'e.email',
                'e.invoicing_status',
                'e.block_account',
                'e.logo',
                'ci.certificate',
                'ci.environment'
            )
            ->where('e.status', '1')
            ->when($request->filled('estado'), function ($query) use ($request) {
                $query->where('e.block_account', $request->get('estado'));
            })
            ->get();

        return DataTables::of($companies)->make(true);
    }

    public function exportar()
    {
        $companies = DB::table('companies as e')
            ->join('tenants as t', 'e.tenant_id', 't.id')
            ->join('plans as p', 'p.id', 'e.plan')
            ->leftJoin('company_invoice as ci', 'ci.company_id', 'e.id')
            ->select(
                't.domain',
                'e.ruc',
                'e.business_name',
                'p.description as plan_name',
                'ci.environment',
                'e.block_account',
                'e.created_at'
            )
            ->where('e.status', '1')
            ->orderBy('e.business_name')
            ->get();

        $filename = 'empresas_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($companies) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Subdominio', 'RUC', 'Razón Social', 'Plan', 'Ambiente', 'Estado', 'Creado']);

            foreach ($companies as $c) {
                fputcsv($handle, [
                    $c->domain,
                    $c->ruc,
                    $c->business_name,
                    $c->plan_name,
                    $c->environment ?? 'DEMO',
                    $c->block_account ? 'BLOQUEADA' : 'ACTIVA',
                    $c->created_at,
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function create(): View
    {
        $all_modules = Module::with('children.grandchildren')->get();
        $departments = Department::all();
        $provinces   = Province::all();
        $districts   = District::all();

        $plans = Plan::select(
            'id',
            'description',
            'price',
            DB::raw('CASE WHEN number_fields > 6 THEN "SIN LÍMITE" ELSE number_fields END AS number_fields'),
        )->get();

        return view('company.create', compact(
            'all_modules',
            'plans',
            'departments',
            'provinces',
            'districts'
        ));
    }

    public function edit($id)
    {
        try {
            return $this->s_manager->edit($id);
        } catch (Throwable $th) {
            Session::flash('message_error', $th->getMessage());
            return back();
        }
    }

    public function store(CompanyStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $tenant = $this->s_manager->store($request->toArray());

            Session::flash('message_success', 'EMPRESA REGISTRADA CON ÉXITO');

            return response()->json(['success' => true, 'message' => 'EMPRESA REGISTRADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::connection('landlord')->rollback();

            if (isset($tenant)) {
                DB::connection('landlord')->statement("DROP DATABASE IF EXISTS `{$tenant->database}`");
            }

            Session::flash('message_error', $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);
        }
    }

    /*
array:17 [▼ // app\Http\Controllers\LandLord\CompanyController.php:315
  "_token"          => "KpM9ktljkPhZJY7m8wwMzd91rXMIXg2U6WZl2dD6"
  "_method"         => "PUT"
  "domain"          => "acerosarequipa.localhost"
  "ruc"             => "20370146994"
  "estado"          => "SIN VERIFICAR"
  "razon_social"            => "CORPORACION ACEROS AREQUIPA S.A."
  "razon_social_abreviada"  => "CORPORACION ACEROS AREQUIPA S.A."
  "ubigeo"                  => null
  "direccion_fiscal"        => null
  "correo"                  => "admin@gmail.com"
  "password"                => "123456789"
  "secondary_user"          => null
  "secondary_password"      => null
  "certificate_password"    => null
  "plan_id"                 => "3"
  "module_id" => array:3 [▼
    0 => "2"
    1 => "3"
    2 => "6"
  ]
  "child_id" => array:5 [▼
    0 => "4"
    1 => "5"
    2 => "6"
    3 => "18"
    4 => "19"
  ]
  --- aveces llega grand_child_id ---
]
*/
    public function update(CompanyUpdateRequest $request, $id)
    {
        try {
            $this->s_manager->update($request->toArray(), $id);

            Session::flash('message_success', 'EMPRESA ACTUALIZADA CON ÉXITO');

            return response()->json(['success' => true, 'message' => 'EMPRESA ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);
        }
    }

    public function resetearClave(Request $request)
    {
        DB::beginTransaction();
        try {
            $company_id = $request->get('company_id');

            $tenant_data = DB::select('select
                                c.ruc,
                                t.database
                                from tenants as t
                                inner join companies as c on c.tenant_id = t.id
                                where c.id = ?', [$company_id])[0];

            DB::table("$tenant_data->database.users as u")
                ->where('u.id', '1')
                ->update([
                    'u.password'         => Hash::make($tenant_data->ruc),
                    'u.password_visible' => $tenant_data->ruc,
                ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CLAVE RESETEADA CON ÉXITO!!!']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function blockAccount(Request $request, $id)
    {
        try {
            $company_landlord = $this->s_manager->blockAccount($request->toArray(), $id);
            $message = $company_landlord->block_account ? 'EMPRESA BLOQUEADA CON ÉXITO' : 'EMPRESA ACTIVADA CON ÉXITO';

            return response()->json(['success' => true, 'message' => $message]);
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
        }
    }
}
