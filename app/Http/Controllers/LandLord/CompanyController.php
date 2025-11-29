<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyStoreRequest;
use App\Models\Company;
use App\Models\CompanyInvoice;
use App\Models\Landlord\Company as LandlordCompany;
use App\Models\Module;
use App\Models\ModuleChild;
use App\Models\ModuleGrandChild;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Tenant\DocumentSerialization;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
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
                't.id',
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

    public function edit($id): View
    {

        $all_modules    =   Module::with('children.grandchildren')->get();

        $tenant_data    =   DB::select('select
                            c.ruc,
                            t.database
                            from tenants as t
                            inner join companies as c on c.tenant_id = t.id
                            where c.id = ?', [$id])[0];

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
            ->get()[0];

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
            ->get()[0];

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

            if ($request->hasFile('certificate_url')) {
                $imagen = $request->file('certificate_url');
                $fileFolderPath = 'assets/img/certificado/';
                $nombreImagen = $imagen->getClientOriginalName();
                $suffix = 1;
                $fileNameWithoutExtension = pathinfo($nombreImagen, PATHINFO_FILENAME);
                while (CompanyInvoice::where('certificate_url', $nombreImagen)->exists()) {
                    $fileName = $fileNameWithoutExtension . "($suffix)." . $imagen->getClientOriginalExtension();
                    $suffix++;
                    $nombreImagen = $fileName;
                }
                $imagen->move(public_path($fileFolderPath), $nombreImagen);
                $company->certificate = $nombreImagen;
                $company->certificate_url = $fileFolderPath . $nombreImagen;
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
                'tenant_id'     => $tenant->id,
                'files_route'   => "{$domain}_{$tenant->id}"
            ]);

            $this->insertDataTenant($tenant->database, $request);

            //======= CREAR CARPETA DE ARCHIVOS PARA EL TENANT  EN PUBLIC/STORAGE/ ====
            $tenantDirectory = "{$domain}_{$tenant->id}";
            if (!Storage::disk('public')->exists($tenantDirectory)) {
                Storage::disk('public')->makeDirectory($tenantDirectory);
            }

            //========== HABILITAR SSL PARA EL SUBDOMINIO DEL TENANT =====
            /*$env = env('APP_ENV');

            if($env === 'production'){
                $mainDomain = 'eldeportivo.online';
                $command    = "sudo certbot --expand -d {$mainDomain} -d www.{$mainDomain} -d {$domain}.{$mainDomain}";
                exec($command, $output, $resultCode);
            }

            if ($resultCode !== 0) {
                dd('Error al generar el certificado subdmonio', $output, $resultCode);
            }*/

            Session::flash('message_success','EMPRESA REGISTRADA CON ÉXITO');
            return response()->json(['success' => true, 'message' => 'EMPRESA REGISTRADA CON ÉXITO']);
        } catch (Exception $ex) {
            DB::rollback();
            Session::flash('message_error', $ex->getMessage());
            return response()->json(['success' => false, 'message' => $ex->getMessage()]);
        }
    }

    private function insertDataTenant($database, $request)
    {
        DB::statement("use $database");

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
            $imagen = $request->file('certificate_url');
            $fileFolderPath = 'assets/img/certificado/';
            $nombreImagen = $imagen->getClientOriginalName();
            $suffix = 1;
            $fileNameWithoutExtension = pathinfo($nombreImagen, PATHINFO_FILENAME);
            while (CompanyInvoice::where('certificate_url', $nombreImagen)->exists()) {
                $fileName = $fileNameWithoutExtension . "($suffix)." . $imagen->getClientOriginalExtension();
                $suffix++;
                $nombreImagen = $fileName;
            }
            $imagen->move(public_path($fileFolderPath), $nombreImagen);
            $company->certificate = $nombreImagen;
            $company->certificate_url = $fileFolderPath . $nombreImagen;
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

        //========= CREANDO USUARIO PARA EL TENANT ========
        /*$user                     =   new User();
        $user->name                 =   'SUPERADMIN';
        $user->email                =   $request->get("correo");
        $user->password             =   Hash::make($request->get("password"));
        $user->password_visible     =   $request->get("password");
        $user->save();

        $role = Role::where('name', 'admin')->first();
        $user->assignRole($role);*/

        DB::table("document_serializations")->insert([
            // ['company_id' => $company->id, 'document_type_id' => '01', 'serie' => 'F001', 'number_limit' => 8, 'destiny' => 'VENTAS', 'default' => 'NO', 'final_number' => 0],
            // ['company_id' => $company->id, 'document_type_id' => '03', 'serie' => 'B001', 'number_limit' => 8, 'destiny' => 'VENTAS', 'default' => 'NO', 'final_number' => 0],
            // ['company_id' => $company->id, 'document_type_id' => '06', 'serie' => 'FF01', 'number_limit' => 8, 'destiny' => 'FNC', 'default' => 'NO', 'final_number' => 0],
            // ['company_id' => $company->id, 'document_type_id' => '07', 'serie' => 'BB01', 'number_limit' => 8, 'destiny' => 'FNC', 'default' => 'NO', 'final_number' => 0],
            // ['company_id' => $company->id, 'document_type_id' => '08', 'serie' => 'FD01', 'number_limit' => 8, 'destiny' => 'FND', 'default' => 'NO', 'final_number' => 0],
            // ['company_id' => $company->id, 'document_type_id' => '09', 'serie' => 'T001', 'number_limit' => 8, 'destiny' => 'GUIAS', 'default' => 'NO', 'final_number' => 0],
            //['company_id' => $company->id, 'document_type_id' => '80', 'serie' => 'NV01', 'descriptión' => 'NOTA DE VENTA','start_number'=>'1', 'number_limit' => 8, 'destiny' => 'VENTAS', 'default' => 'NO', 'final_number' => 0,'initiated'=>'NO'],
            ['company_id' => $company->id, 'document_type_id' => '50', 'serie' => 'TV01', 'number_limit' => 8, 'destiny' => 'VENTAS', 'default' => 'SI', 'final_number' => 0],
            ['company_id' => $company->id, 'document_type_id' => '52', 'serie' => 'NI01', 'number_limit' => 8, 'destiny' => 'NOTAS', 'default' => 'NO', 'final_number' => 0],
            ['company_id' => $company->id, 'document_type_id' => '53', 'serie' => 'NS01', 'number_limit' => 8, 'destiny' => 'NOTAS', 'default' => 'NO', 'final_number' => 0],
        ]);

        $serialization                      =   new DocumentSerialization();
        $serialization->company_id          =   1;
        $serialization->document_type_id    =   80;
        $serialization->serie               =   'NV01';
        $serialization->description         =   'NOTA DE VENTA';
        $serialization->start_number        =   '1';
        $serialization->number_limit        =   8;
        $serialization->destiny             =   NULL;
        $serialization->default             =   'NO';
        $serialization->final_number        =   0;
        $serialization->initiated           =   'NO';
        $serialization->save();

        foreach ($this->modules as $module) {
            Module::create([
                'id' => $module->id,
                'description' => $module->description,
                'order' => $module->order,
            ]);
        }

        foreach ($this->children as $children) {
            ModuleChild::create([
                'id' => $children->id,
                'module_id' => $children->module_id,
                'description' => $children->description,
                'route_name' => $children->route_name,
                'order' => $children->order,
            ]);
        }

        foreach ($this->grand_children as $grand_children) {
            ModuleGrandChild::create([
                'id' => $grand_children->id,
                'module_child_id' => $grand_children->module_child_id,
                'description' => $grand_children->description,
                'route_name' => $grand_children->route_name,
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
            $tenant_data    =   DB::select('select
                                c.ruc,
                                t.database
                                from tenants as t
                                inner join companies as c on c.tenant_id = t.id
                                where c.id = ?', [$id])[0];


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
                        'id'            => $module->id,
                        'description'   => $module->description,
                        'order'         => $module->order,
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now(),
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
                        'u.password'           =>  Hash::make($request->get('password')),
                        'u.password_visible'    =>  $request->get('password'),
                        'u.email'               =>  $request->get("correo")
                    ]
                );

            DB::commit();

            return to_route("landlord.mantenimientos.empresa");
        } catch (\Exception $ex) {
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
