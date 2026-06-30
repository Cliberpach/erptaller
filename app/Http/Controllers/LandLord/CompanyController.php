<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyStoreRequest;
use App\Models\Company;
use App\Models\Landlord\Company as LandlordCompany;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Module;
use App\Models\ModuleChild;
use App\Models\ModuleGrandChild;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Tenant\Maintenance\Collaborator\Collaborator;
use App\Models\Tenant\Maintenance\Position;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private $modules;
    private $children;
    private $grand_children;
    private $plan;

    public function index()
    {
        // $companies = DB::table('companies as e')
        //     ->join('tenants as t', 'e.tenant_id', 't.id')
        //     ->join('plans as p','p.id','e.plan')
        //     ->select('e.id', 'e.ruc', 'e.business_name', 'e.created_at', 't.id', 't.domain',
        //     'p.description as plan_name','e.email','e.invoicing_status')
        //     ->get();

        return view('company.landlord');
    }


    public function getCompanies(Request $request)
    {

        $companies = DB::table('companies as e')
            ->join('tenants as t', 'e.tenant_id', 't.id')
            ->join('plans as p', 'p.id', 'e.plan')
            ->select(
                'e.id',
                'e.ruc',
                'e.business_name',
                'e.created_at',
                't.id as tenant_id',
                't.domain',
                'p.description as plan_name',
                'e.email',
                'e.invoicing_status'
            )
            ->where('status', '1')
            ->get();

        return DataTables::of($companies)->make(true);
    }


    public function create(): View
    {
        $all_modules = Module::with('children.grandchildren')->get();

        $plans = Plan::select(
            'id',
            'description',
            'price',
            DB::raw('CASE WHEN number_fields > 6 THEN "SIN LÍMITE" ELSE number_fields END AS number_fields'),
        )->get();

        return view('company.create', compact('all_modules', 'plans'));
    }

    public function edit($id): View|RedirectResponse
    {

        $all_modules    =   Module::with('children.grandchildren')->get();

        $tenant_data    =   DB::select('select
                            c.ruc,
                            t.database
                            from tenants as t
                            inner join companies as c on c.tenant_id = t.id
                            where c.id = ?', [$id]);

        if (empty($tenant_data)) {
            return redirect()->route('landlord.mantenimientos.empresa')
                ->with('message_error', 'La empresa solicitada no existe.');
        }

        $tenant_data = $tenant_data[0];

        $tenant_modules =   DB::table("$tenant_data->database.modules as m")
            ->select('m.id')
            ->get();

        $tenant_modules_children    =   DB::table("$tenant_data->database.module_children as mc")
            ->select('mc.id')
            ->get();

        $tenant_modules_grand_children   =   DB::table("$tenant_data->database.module_grand_children as mgc")
            ->select('mgc.id')
            ->get();

        $user   =   DB::table("$tenant_data->database.users as u")
            ->select('u.*')
            ->where('u.id', 1)
            ->first();

        if (! $user) {
            return redirect()->route('landlord.mantenimientos.empresa')
                ->with('message_error', 'El usuario administrador del tenant no existe.');
        }

        $company        =   DB::table("companies as c")
            ->join('tenants as t', 't.id', '=', 'c.tenant_id')
            ->select(
                'c.id',
                't.domain',
                'c.ruc',
                'c.business_name',
                'c.abbreviated_business_name',
                'zip_code',
                'fiscal_address',
                'c.plan'
            )
            ->where('c.id', $id)
            ->first();

        if (! $company) {
            return redirect()->route('landlord.mantenimientos.empresa')
                ->with('message_error', 'La empresa solicitada no existe.');
        }

        $plans = Plan::select(
            'id',
            'description',
            'price',
            DB::raw('CASE WHEN number_fields > 6 THEN "SIN LÍMITE" ELSE number_fields END AS number_fields'),
        )->get();


        return view('company.edit_company_landlord', compact(
            'all_modules',
            'plans',
            'company',
            'tenant_modules',
            'tenant_modules_children',
            'tenant_modules_grand_children',
            'user'
        ));
    }

    public function store(CompanyStoreRequest $request)
    {
        try {

            DB::beginTransaction();

            $domain = strtolower($request->get("domain"));
            $tenant = Tenant::create([
                "name" => $request->input('razon_social'),
                "domain" => $domain . "." . parse_url(config("app.url"), PHP_URL_HOST),
            ]);
            $tenantDirectory = "{$domain}_{$tenant->id}";

            $company                                =   new Company();
            $company->tenant_id                     =   $tenant->id;
            $company->ruc                           =   $request->get("ruc");
            $company->business_name                 =   $request->get("razon_social");
            $company->abbreviated_business_name     =   $request->get("razon_social_abreviada");
            $company->zip_code                      =   $request->get("ubigeo");
            $company->fiscal_address                =   $request->get("direccion_fiscal");
            $company->email                         =   $request->get("correo");
            $company->plan                          =   $request->get("plan_id");
            $company->files_route                   =   "{$domain}_{$tenant->id}";
            $company->token_placa                   =  "nsHeEpNSOBr8ucEFnL7OtKmVkZhefUuvoM8O1Lz7uFEOi4KtFZ54==";

            //=========== CERTIFICADO ==========
            if ($request->hasFile('certificate_url')) {
                $certificate                =   $request->file('certificate_url');
                $fileFolderPath             =   'storage/' . $tenantDirectory . '/certificate/';
                $fileName                   =   $tenant->name . '_' . $tenant->id . "_" . $certificate->getClientOriginalExtension();

                $company->certificate       =   $fileName;
                $company->certificate_url   =   $fileFolderPath . $fileName;
            }

            $company->save();

            $module_array       = $request->module_id;
            $child_array        = $request->child_id;
            $grandchild_array   = $request->grandchild_id;

            $this->modules          = Module::whereIn('id', $module_array)->get();
            $this->children         = ModuleChild::whereIn('id', $child_array)->get();
            $this->grand_children   = ModuleGrandChild::whereIn('id', $grandchild_array)->get();
            $this->plan             = Plan::findOrFail($company->plan);

            DB::commit();

            $request->merge([
                'tenant_id'     =>  $tenant->id,
                'tenant_name'   =>  $tenant->name,
                'files_route'   =>  "{$domain}_{$tenant->id}"
            ]);

            $this->insertDataTenant($tenant->database, $request);

            //======= CREAR CARPETA DE ARCHIVOS PARA EL TENANT  EN PUBLIC/STORAGE/ ====
            if (!Storage::disk('public')->exists($tenantDirectory)) {
                Storage::disk('public')->makeDirectory($tenantDirectory);
            }


            Session::flash('message_success', 'EMPRESA REGISTRADA CON ÉXITO');
            return response()->json(['success' => true, 'message' => 'EMPRESA REGISTRADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollback();
            Session::flash('message_error', $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);
        }
    }

    private function insertDataTenant($database, $request)
    {
        $files_route            =   $request->get('files_route');

        Tenant::where('database', $database)->firstOrFail()->makeCurrent();

        DB::setDefaultConnection('tenant');

        $company                                =   new Company();
        $company->ruc                           =   $request->get("ruc");
        $company->domain                        =   $request->get('domain');
        $company->tenant_id                     =   $request->get('tenant_id');
        $company->files_route                   =   $request->get('files_route');
        $company->business_name                 =   $request->get("razon_social");
        $company->abbreviated_business_name     =   $request->get("razon_social_abreviada");
        $company->zip_code                      =   $request->get("ubigeo");
        $company->fiscal_address                =   $request->get("direccion_fiscal");
        $company->email                         =   $request->get("correo");
        $company->plan                          =   $request->get("plan_id");

        if ($request->hasFile('certificate_url')) {
            $certificate                = $request->file('certificate_url');
            $fileFolderPath             = 'storage/' . $files_route . '/certificate/';
            $fileName                   = $files_route . "." . $certificate->getClientOriginalExtension();

            $certificate->move(public_path($fileFolderPath), $fileName);
            $company->certificate       = $fileName;
            $company->certificate_url   = $fileFolderPath . $fileName;
        }

        $company->save();

        //========= DATOS DE FACTURACIÓN COMPANY INVOICE =========
        DB::table('company_invoices')->insert([
            'company_id'           => $company->id,
            'plan'                 => $company->plan,
            'environment'          => 'DEMO',
            'department_id'        => '01',
            'province_id'          => '0101',
            'district_id'          => '010101',
            'department_name'      => 'LA LIBERTAD',
            'province_name'        => 'TRUJILLO',
            'district_name'        => 'TRUJILLO',
            'ubigeo'               => '130101',
            'urbanization'         => 'PALERMO',
            'local_code'           => '0000',
            'secondary_user'       => 'MODDATOS',
            'secondary_password'   => 'MODDATOS',
            'api_user_gre'         => 'test-85e5b0ae-255c-4891-a595-0b98c65c9854',
            'api_password_gre'    => 'test-Hty/M6QshYvPgItX2P0+Kw==',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        //========= SEDE PRINCIPAL DEL TENANT (Fase 3 - Multi-Sede) ========
        $sede = \App\Models\Tenant\Sede::create([
            'numero'       => 1,
            'nombre'       => 'SEDE PRINCIPAL',
            'codigo'       => 'S001',
            'es_principal' => true,
            'direccion'    => $request->get('direccion_fiscal'),
            'ubigeo'       => $request->get('ubigeo'),
            'status'       => 'ACTIVO',
        ]);

        //========= ALMACÉN PRINCIPAL DE LA SEDE PRINCIPAL (Multi-Sede Etapa 3a) ========
        // Queda como id=1 (no hay otro almacén antes; WarehouseSeeder fue removido)
        // → el warehouse_id=1 hardcodeado del flujo de stock sigue funcionando sin regresión.
        \App\Models\Tenant\Warehouse::create([
            'sede_id'      => $sede->id,
            'descripcion'  => 'ALMACÉN PRINCIPAL',
            'es_principal' => true,
            'estado'       => 'ACTIVO',
        ]);

        //========= SERIES DE LA SEDE PRINCIPAL (Multi-Sede Etapa 5) ========
        // Antes este sembrado estaba comentado → ningún tenant recibía series. Ahora la sede
        // principal recibe sus series (sufijo 001) vía el helper único (mismo que SedeController).
        (new \App\Http\Services\Tenant\Maintenance\SerieService())->generarSeriesSede($sede);

        //========= CAJA DE LA SEDE PRINCIPAL (Multi-Sede Etapa 4) ========
        // Paralelo al almacén/series. La caja nace solo con sede + nombre (sin combo).
        // Reemplaza la "CAJA PRINCIPAL" global que sembraba PettyCashSeeder (ya removido).
        (new \App\Http\Services\Tenant\Cash\CajaService())->crearCajaSede($sede);

        //========= 3 USUARIOS POR DEFECTO DEL TENANT (admin / ventas / tecnico) ========
        // Se usa App\Models\User (modelo de auth) para que el model_type del rol quede alineado.
        // Los 3 quedan asignados a la sede principal con es_default = true.
        $usuarios = [
            ['name' => 'ADMIN',   'email' => $request->get('correo'),   'password' => $request->get('password'), 'doc' => '99999999', 'cargo' => 'ADMINISTRADOR', 'rol' => 'admin',   'full_name' => 'ADMINISTRADOR'],
            ['name' => 'VENTAS',  'email' => 'ventas@tallersuite.com',  'password' => '123456789',               'doc' => '88888888', 'cargo' => 'VENTAS',        'rol' => 'ventas',  'full_name' => 'VENDEDOR'],
            ['name' => 'TECNICO', 'email' => 'tecnico@tallersuite.com', 'password' => '123456789',               'doc' => '77777777', 'cargo' => 'TECNICO',       'rol' => 'tecnico', 'full_name' => 'TECNICO'],
        ];

        foreach ($usuarios as $u) {
            $position = Position::firstOrCreate(['name' => $u['cargo']]);

            $collaborator                               =   new Collaborator();
            $collaborator->full_name                    =   $u['full_name'];
            $collaborator->document_type_id             =   1;
            $collaborator->document_number              =   $u['doc'];
            $collaborator->address                      =   $request->get('direccion_fiscal');
            $collaborator->phone                        =   '999999999';
            $collaborator->work_days                    =   30;
            $collaborator->rest_days                    =   0;
            $collaborator->monthly_salary               =   1500;
            $collaborator->daily_salary                 =   50;
            $collaborator->position_id                  =   $position->id;
            $collaborator->document_type_abbreviation   =   'DNI';
            $collaborator->save();

            $user                       =   new User();
            $user->name                 =   $u['name'];
            $user->email                =   $u['email'];
            $user->password             =   Hash::make($u['password']);
            $user->password_visible     =   $u['password'];
            $user->collaborator_id      =   $collaborator->id;
            $user->save();

            $role = Role::firstOrCreate(['name' => $u['rol']]);
            $user->assignRole($role);

            $user->sedes()->attach($sede->id, ['es_default' => true]);
        }

        //========= CAJA FICTICIA (placeholder de sistema) ========
        // La facturación de una OT atribuye la venta a esta caja FICTICIO (status ANULADO)
        // para que NO cuente en el cuadre de una caja real (SaleDto::getDtoStoreFromOrder la lee).
        // Acá los usuarios + turnos ya existen (el book exige shift_id/user_id). Idempotente.
        $ficticioId = DB::table('petty_cashes')->where('type', 'FICTICIO')->value('id');
        if (! $ficticioId) {
            $ficticioId = DB::table('petty_cashes')->insertGetId([
                'name'       => 'CAJA FICTICIA',
                'type'       => 'FICTICIO',
                'status'     => 'ANULADO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (! DB::table('petty_cash_books')->where('type', 'FICTICIO')->exists()) {
            DB::table('petty_cash_books')->insert([
                'petty_cash_id'   => $ficticioId,
                'petty_cash_name' => 'CAJA FICTICIA',
                'shift_id'        => DB::table('shifts')->value('id'),
                'user_id'         => DB::table('users')->value('id'),
                'status'          => 'ANULADO',
                'initial_amount'  => 0,
                'initial_date'    => now(),
                'type'            => 'FICTICIO',
            ]);
        }

        //========= CLIENTE VARIOS POR DEFECTO (per-tenant, customers ahora vive en el tenant) ========
        \App\Models\Landlord\Customer::firstOrCreate(
            ['es_varios' => true],
            [
                'name'                       => 'CLIENTE VARIOS',
                'document_number'            => '99999999',
                'type_identity_document_id'  => 1,
                'type_document_code'         => '01',
                'type_document_name'         => 'DOCUMENTO NACIONAL DE IDENTIDAD',
                'type_document_abbreviation' => 'DNI',
                'status'                     => 'ACTIVO',
            ]
        );

        // Multi-Sede Etapa 5: las series ya se generan (las 7, por sede) en generarSeriesSede()
        // arriba. Se eliminó el insert legado de una sola serie NV01 colgada de company_id.

        foreach ($this->modules as $module) {
            Module::create([
                'id' => $module->id,
                'description' => $module->description,
                'order' => $module->order,
                'icon'  =>  $module->icon
            ]);
        }

        foreach ($this->children as $children) {
            ModuleChild::create([
                'id' => $children->id,
                'module_id' => $children->module_id,
                'description' => $children->description,
                'route_name' => $children->route_name,
                'permission' => $children->permission, // Permisos P1: copia el mapeo al tenant
                'order' => $children->order,
            ]);
        }

        foreach ($this->grand_children as $grand_children) {
            ModuleGrandChild::create([
                'id' => $grand_children->id,
                'module_child_id' => $grand_children->module_child_id,
                'description' => $grand_children->description,
                'route_name' => $grand_children->route_name,
                'permission' => $grand_children->permission, // Permisos P1: copia el mapeo al tenant
                'order' => $grand_children->order,
            ]);
        }

        Plan::create([
            'id' => $this->plan->id,
            'description' => $this->plan->description,
            'number_fields' => $this->plan->number_fields,
            'price' => $this->plan->price,
        ]);
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
    public function update(CompanyStoreRequest $request, $id)
    {
        try {

            DB::beginTransaction();

            //======== OBTENEMOS EL NOMBRE DEL TENANT ===========
            $tenant_data    =   DB::select('SELECT
                                c.ruc,
                                t.database
                                FROM tenants as t
                                INNER JOIN companies as c on c.tenant_id = t.id
                                WHERE c.id = ?', [$id])[0];


            //========== ACTUALIZAR DATOS DE LA EMPRESA TENANT =======
            $company                                = Company::find($id);
            $company->ruc                           = $request->get("ruc");
            $company->business_name                 = $request->get("razon_social");
            $company->abbreviated_business_name     = $request->get("razon_social_abreviada");
            $company->zip_code                      = $request->get("ubigeo");
            $company->fiscal_address                = $request->get("direccion_fiscal");
            $company->email                         = $request->get("correo");
            $company->plan                          = $request->get("plan_id");

            // if ($request->hasFile('certificate_url')) {
            //     $imagen = $request->file('certificate_url');
            //     $fileFolderPath = 'assets/img/certificado/';
            //     $nombreImagen = $imagen->getClientOriginalName();
            //     $suffix = 1;
            //     $fileNameWithoutExtension = pathinfo($nombreImagen, PATHINFO_FILENAME);
            //     while (CompanyInvoice::where('certificate_url', $nombreImagen)->exists()) {
            //         $fileName = $fileNameWithoutExtension . "($suffix)." . $imagen->getClientOriginalExtension();
            //         $suffix++;
            //         $nombreImagen = $fileName;
            //     }
            //     $imagen->move(public_path($fileFolderPath), $nombreImagen);
            //     $company->certificate = $nombreImagen;
            //     $company->certificate_url = $fileFolderPath . $nombreImagen;
            // }

            $company->save();


            //======== ACTUALIZAR MÓDULOS DE LA EMPRESA TENANT =======
            $module_array       = $request->module_id ?? [];
            $child_array        = $request->child_id ?? [];
            $grandchild_array   = $request->grandchild_id ?? [];


            $this->modules          = count($module_array) > 0 ? Module::whereIn('id', $module_array)->get() : [];
            $this->children         = count($child_array) > 0 ? ModuleChild::whereIn('id', $child_array)->get() : [];
            $this->grand_children   = count($grandchild_array) > 0 ? ModuleGrandChild::whereIn('id', $grandchild_array)->get() : [];

            DB::table("$tenant_data->database.modules")->delete();

            DB::table("$tenant_data->database.module_children")->delete();

            DB::table("$tenant_data->database.module_grand_children")->delete();

            DB::table("$tenant_data->database.plans")->delete();

            foreach ($this->modules as $module) {
                DB::table("$tenant_data->database.modules")
                    ->insert([
                        'id'            =>  $module->id,
                        'description'   =>  $module->description,
                        'order'         =>  $module->order,
                        'created_at'    =>  Carbon::now(),
                        'updated_at'    =>  Carbon::now(),
                        'icon'          =>  $module->icon
                    ]);
            }

            foreach ($this->children as $children) {
                DB::table("$tenant_data->database.module_children")
                    ->insert([
                        'id'            => $children->id,
                        'module_id'     => $children->module_id,
                        'description'   => $children->description,
                        'route_name'    => $children->route_name,
                        'order'         => $children->order,
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now(),
                    ]);
            }

            foreach ($this->grand_children as $grand_children) {
                DB::table("$tenant_data->database.module_grand_children")
                    ->insert([
                        'id' => $grand_children->id,
                        'module_child_id' => $grand_children->module_child_id,
                        'description' => $grand_children->description,
                        'route_name' => $grand_children->route_name,
                        'order' => $grand_children->order,
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now(),
                    ]);
            }

            //======== ACTUALIZAR PLAN DE LA EMPRESA TENANT =======
            $this->plan             = Plan::findOrFail($company->plan);

            DB::table("$tenant_data->database.plans")
                ->insert([
                    'id'                => $this->plan->id,
                    'description'       => $this->plan->description,
                    'number_fields'     => $this->plan->number_fields,
                    'price'             => $this->plan->price,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]);

            //======== ACTUALIZAR CORREO Y CONTRASEÑA DEL TENANT ========
            DB::table("$tenant_data->database.users as u")
                ->where('u.id', '1')
                ->update(
                    [
                        'u.password'            =>  Hash::make($request->get('password')),
                        'u.password_visible'    =>  $request->get('password'),
                        'u.email'               =>  $request->get("correo")
                    ]
                );

            DB::commit();

            return to_route("landlord.mantenimientos.empresa");
        } catch (Throwable $ex) {
            DB::rollback();
            return redirect()->back()->with("error", $ex->getMessage() . '-LINE:' . $ex->getLine());
        }
    }


    /*
array:1 [ // app\Http\Controllers\LandLord\CompanyController.php:263
  "company_id" => "1"
]
*/
    public function resetearClave(Request $request)
    {
        DB::beginTransaction();
        try {

            $company_id     =   $request->get('company_id');

            $tenant_data    =   DB::select('select
                                c.ruc,
                                t.database
                                from tenants as t
                                inner join companies as c on c.tenant_id = t.id
                                where c.id = ?', [$company_id])[0];


            DB::table("$tenant_data->database.users as u")
                ->where('u.id', '1')
                ->update(['u.password' => Hash::make($tenant_data->ruc)]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CLAVE RESETEADA CON ÉXITO!!!']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function deleteTenant($id)
    {

        try {

            //====== OBTENER EMPRESA =======
            $company = LandlordCompany::find($id);

            if (!$company) {
                throw new Exception("NO EXISTE LA EMPRESA EN LA BD!!");
            }

            //====== OBTENER DATOS DEL TENANT =======
            $tenant_data = DB::select('select
                                            c.ruc,
                                            t.database
                                            from tenants as t
                                            inner join companies as c on c.tenant_id = t.id
                                            where c.id = ?', [$id])[0];

            //======== VERIFICAR SI EXISTE LA BD DEL TENANT =======
            $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$tenant_data->database]);
            if (!$exists) {
                throw new Exception("NO EXISTE LA BD DEL TENANT!!");
            }

            //===== ELIMINAR LA BD DEL TENANT =======
            DB::statement("DROP DATABASE IF EXISTS {$tenant_data->database}");

            //======= ELIMINAR ARCHIVOS DEL TENANT ======
            $path_directory_tenant = public_path('storage/' . $company->files_route);
            if (File::exists($path_directory_tenant) && File::isDirectory($path_directory_tenant)) {
                File::deleteDirectory($path_directory_tenant);
            }

            //====== DESACTIVAR LA EMPRESA ========
            $company->status = '0';
            $company->update();

            return response()->json(['success' => true, 'message' => 'EMPRESA ELIMINADA!!!']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
