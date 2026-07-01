<?php

namespace App\Http\Services\Landlord\Maintenance\Company;

use App\Http\Controllers\UtilController;
use App\Http\Services\Tenant\Cash\CajaService;
use App\Http\Services\Tenant\Maintenance\SerieService;
use App\Models\Landlord\Company;
use App\Models\Landlord\CompanyInvoice;
use App\Models\Landlord\Customer;
use App\Models\Tenant;
use App\Models\Tenant\Maintenance\Collaborator\Collaborator;
use App\Models\Tenant\Maintenance\Company\Company as TenantCompany;
use App\Models\Tenant\Maintenance\Company\CompanyInvoice as TenantCompanyInvoice;
use App\Models\Tenant\Maintenance\Position;
use App\Models\Tenant\Sede;
use App\Models\Tenant\User as TenantUser;
use App\Models\Tenant\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Throwable;
use Illuminate\Support\Facades\Cache;

class CompanyService
{
    private CompanyDto $s_dto;
    private CompanyRepository $s_repository;
    private CompanyValidation $s_validation;

    public function __construct()
    {
        $this->s_dto         = new CompanyDto();
        $this->s_repository  = new CompanyRepository();
        $this->s_validation  = new CompanyValidation();
    }

    public function store(array $data): Tenant
    {
        $this->s_validation->validationStore($data);

        $dto_tenant = $this->s_dto->getDtoTenant($data);
        $tenant     = $this->s_repository->storeTenant($dto_tenant);

        $dto_company      = $this->s_dto->getDtoCompanyLandlord($data, $tenant);
        $company_landlord = $this->s_repository->storeCompanyLandlord($dto_company);

        $dto_company_invoice_landlord = $this->s_dto->getDtoCompanyInvoiceLandlord($data, $company_landlord);
        $company_invoice_landlord     = $this->s_repository->storeCompanyInvoiceLandlord($dto_company_invoice_landlord);

        $data['files_route'] = $company_landlord->files_route;
        $data['tenant_id']   = $tenant->id;

        DB::connection('landlord')->commit();

        $data_tenant = $this->insertDataTenant($tenant, $data);

        $this->makeTenantFilesSpace($company_landlord);
        Tenant::forgetCurrent();

        if (isset($data['logo'])) {
            $this->saveLogo($data['logo'], $tenant, $company_landlord, $data_tenant->company_tenant);
        }

        if (isset($data['certificate'])) {
            $this->saveCertificate($data, $tenant, $company_invoice_landlord, $data_tenant->company_invoice_tenant);
        }

        return $tenant;
    }

    public function insertDataTenant(Tenant $tenant, array $data)
    {
        $tenant->makeCurrent();

        $dto_tenant_company = $this->s_dto->getDtoTenantCompany($data);
        $tenant_company     = $this->s_repository->storeCompanyTenant($dto_tenant_company);

        $dto_tenant_company_invoice = $this->s_dto->getDtoTenantCompanyInvoice($data, $tenant_company);
        $tenant_company_invoice     = $this->s_repository->storeCompanyInvoiceTenant($dto_tenant_company_invoice);

        $dto_document_serialization = $this->s_dto->getDtoDocumentSerializationTenant($tenant_company->id);
        $this->s_repository->storeDocumentSerializationTenant($dto_document_serialization);

        $collaborator_tenant = $this->s_repository->getCollaboratorAdminTenant();

        $dto_user_tenant = $this->s_dto->getDtoUserTenant($data, $collaborator_tenant->id);
        $this->s_repository->updateUserAdminTenant($dto_user_tenant);

        $this->seedSedePrincipal($data);

        return (object) [
            'company_tenant'         => $tenant_company,
            'company_invoice_tenant' => $tenant_company_invoice,
        ];
    }

    /**
     * Provisioning Multi-Sede de la sede principal del tenant recién creado: sede, almacén,
     * series (7 por sede), caja, los 2 usuarios adicionales (ventas/tecnico) y cliente varios.
     * El admin (id=1) ya fue creado/actualizado arriba (placeholder del template); acá solo
     * se enlaza a la sede. Corre después de crear el admin para que la caja ficticia pueda
     * completar su book (necesita shift_id + user_id ya existentes).
     */
    private function seedSedePrincipal(array $data): void
    {
        $sede = Sede::create([
            'numero'       => 1,
            'nombre'       => 'SEDE PRINCIPAL',
            'codigo'       => 'S001',
            'es_principal' => true,
            'direccion'    => $data['direccion_fiscal'],
            'ubigeo'       => $data['ubigeo'],
            'status'       => 'ACTIVO',
        ]);

        Warehouse::create([
            'sede_id'      => $sede->id,
            'descripcion'  => 'ALMACÉN PRINCIPAL',
            'es_principal' => true,
            'estado'       => 'ACTIVO',
        ]);

        (new SerieService())->generarSeriesSede($sede);
        (new CajaService())->crearCajaSede($sede);

        $admin = TenantUser::find(1);
        if ($admin) {
            $admin->sedes()->attach($sede->id, ['es_default' => true]);
        }

        $usuarios = [
            ['name' => 'VENTAS',  'email' => 'ventas@tallersuite.com',  'password' => '123456789', 'doc' => '88888888', 'cargo' => 'VENTAS',  'rol' => 'ventas',  'full_name' => 'VENDEDOR'],
            ['name' => 'TECNICO', 'email' => 'tecnico@tallersuite.com', 'password' => '123456789', 'doc' => '77777777', 'cargo' => 'TECNICO', 'rol' => 'tecnico', 'full_name' => 'TECNICO'],
        ];

        foreach ($usuarios as $u) {
            $position = Position::firstOrCreate(['name' => $u['cargo']]);

            $collaborator                             = new Collaborator();
            $collaborator->full_name                  = $u['full_name'];
            $collaborator->document_type_id           = 1;
            $collaborator->document_number             = $u['doc'];
            $collaborator->address                    = $data['direccion_fiscal'];
            $collaborator->phone                      = '999999999';
            $collaborator->work_days                   = 30;
            $collaborator->rest_days                   = 0;
            $collaborator->monthly_salary              = 1500;
            $collaborator->daily_salary                = 50;
            $collaborator->position_id                 = $position->id;
            $collaborator->document_type_abbreviation  = 'DNI';
            $collaborator->save();

            $user                   = new TenantUser();
            $user->name              = $u['name'];
            $user->email             = $u['email'];
            $user->password          = Hash::make($u['password']);
            $user->password_visible  = $u['password'];
            $user->collaborator_id   = $collaborator->id;
            $user->save();

            $role = Role::firstOrCreate(['name' => $u['rol']]);
            $user->assignRole($role);

            $user->sedes()->attach($sede->id, ['es_default' => true]);
        }

        //========= CAJA FICTICIA (placeholder de sistema) =========
        // El petty_cash ya existe (creado por la migración de backfill contra el template).
        // Acá los usuarios + turnos ya existen -> se completa el book. Idempotente.
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

        //========= CLIENTE VARIOS POR DEFECTO =========
        Customer::firstOrCreate(
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
    }

    public function makeTenantFilesSpace(Company $company)
    {
        if (!Storage::disk('public')->exists($company->files_route)) {
            Storage::disk('public')->makeDirectory($company->files_route);
        }
    }

    public function saveLogo($logo, Tenant $tenant, Company $company_landlord, TenantCompany $company_tenant)
    {
        UtilController::saveFileFromLandlord($logo, 'logo_principal', $company_landlord->files_route . '/logo');

        $logo_name = 'logo_principal.' . $logo->getClientOriginalExtension();
        $logo_url  = 'storage/' . $company_landlord->files_route . '/logo/' . $logo_name;
        $this->s_repository->saveLogoLandlord($company_landlord, $logo_url, $logo_name);

        $tenant->makeCurrent();
        $this->s_repository->saveLogoTenant($company_tenant, $logo_url, $logo_name);
        Tenant::forgetCurrent();
    }

    public function saveCertificate(array $data, Tenant $tenant, CompanyInvoice $company_invoice_land, TenantCompanyInvoice $company_invoice_tenant)
    {
        $files_route = $data['files_route'];
        $this->operationCert($data, $files_route);

        $cert_name = 'cert_production.pem';
        $cert_url  = 'storage/' . $files_route . '/greenter/certs/' . $cert_name;
        $this->s_repository->saveCertLandlord($company_invoice_land, $cert_url, $cert_name);

        $tenant->makeCurrent();
        $this->s_repository->saveCertTenant($company_invoice_tenant, $cert_url, $cert_name);
        Tenant::forgetCurrent();
    }

    public function operationCert(array $data, string $files_route)
    {
        if (!isset($data['certificate'])) {
            return;
        }

        $certificateFile      = $data['certificate'];
        $certificate_password = $data['certificate_password'] ?? null;
        $extension            = strtolower($certificateFile->getClientOriginalExtension());

        $pemFilename   = 'cert_production.pem';
        $directoryPath = $files_route . '/greenter/certs/';
        $pemFullPath   = storage_path('app/public/' . $directoryPath . $pemFilename);

        if (!Storage::disk('public')->exists($directoryPath)) {
            Storage::disk('public')->makeDirectory($directoryPath);
        }

        // ===== CASO 1: SUBEN UN .PEM =====
        if ($extension === 'pem') {
            $certificateFile->storeAs($directoryPath, $pemFilename, 'public');
        }

        // ===== CASO 2: SUBEN UN .P12 =====
        if ($extension === 'p12') {
            $this->saveCertP12ToPem($certificateFile, $certificate_password, $pemFullPath);
        }
    }

    public function saveCertP12ToPem($certificateFile, ?string $certificate_password, $pemFullPath)
    {
        $isWindows   = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $opensslPath = $isWindows
            ? '"C:\Program Files\OpenSSL-Win64\bin\openssl.exe"'
            : 'openssl';

        $legacy = $isWindows ? '' : '-legacy ';

        exec($opensslPath . ' version 2>&1', $check, $checkCode);
        if ($checkCode !== 0) {
            throw new Exception('OpenSSL no está disponible en el servidor');
        }

        $tempP12Path    = storage_path('app/temp_' . uniqid() . '.p12');
        $privateKeyPath = storage_path('app/private_' . uniqid() . '.key');
        $certPath       = storage_path('app/cert_' . uniqid() . '.crt');

        $certificateFile->move(dirname($tempP12Path), basename($tempP12Path));

        if (!file_exists($tempP12Path)) {
            throw new Exception('No se pudo guardar el archivo P12 temporal');
        }

        $p12Password = $certificate_password;

        // 1. Extraer CLAVE PRIVADA
        $cmdPrivateKey = $opensslPath . ' pkcs12 ' . $legacy .
            '-in ' . escapeshellarg($tempP12Path) . ' ' .
            '-nocerts -nodes ' .
            '-out ' . escapeshellarg($privateKeyPath) . ' ' .
            '-password pass:' . escapeshellarg($p12Password) . ' 2>&1';

        exec($cmdPrivateKey, $outKey, $codeKey);

        if ($codeKey !== 0) {
            logger()->error('Error extrayendo clave privada', $outKey);
            throw new Exception('Error al extraer la clave privada del P12');
        }

        // 2. Extraer CERTIFICADO DEL CONTRIBUYENTE
        $cmdCert = $opensslPath . ' pkcs12 ' . $legacy .
            '-in ' . escapeshellarg($tempP12Path) . ' ' .
            '-clcerts -nokeys ' .
            '-out ' . escapeshellarg($certPath) . ' ' .
            '-password pass:' . $p12Password . ' 2>&1';

        exec($cmdCert, $outCert, $codeCert);

        if ($codeCert !== 0) {
            logger()->error('Error extrayendo certificado', $outCert);
            throw new Exception('Error al extraer el certificado del P12');
        }

        // 3. UNIR EN UN SOLO PEM (FORMATO SUNAT CORRECTO)
        $cleanPrivateKey = $this->cleanPem(file_get_contents($privateKeyPath));
        $cleanCert       = $this->cleanPem(file_get_contents($certPath));

        file_put_contents($pemFullPath, $cleanPrivateKey . $cleanCert);

        @unlink($tempP12Path);
        @unlink($privateKeyPath);
        @unlink($certPath);
    }

    public function cleanPem($content)
    {
        preg_match_all(
            '/-----BEGIN ([A-Z ]+)-----.*?-----END \1-----/s',
            $content,
            $matches
        );

        return implode(PHP_EOL, $matches[0]) . PHP_EOL;
    }

    public function edit(int $id)
    {
        $all_modules                   = $this->s_repository->getAllModules();
        $tenant_data                   = $this->s_repository->getTenantCompanyData($id);
        $tenant_modules                = $this->s_repository->getTenantModules($tenant_data->database);
        $tenant_modules_children       = $this->s_repository->getTenantModulesChildren($tenant_data->database);
        $tenant_modules_grand_children = $this->s_repository->getTenantModulesGrandChildren($tenant_data->database);

        $user        = $this->s_repository->getTenantUser($tenant_data->database);
        $plans       = $this->s_repository->getPlans();
        $departments = UtilController::getDepartments();
        $provinces   = UtilController::getProvinces();
        $districts   = UtilController::getDistricts();

        return view('company.edit_company_landlord', compact(
            'all_modules',
            'plans',
            'tenant_data',
            'tenant_modules',
            'tenant_modules_children',
            'tenant_modules_grand_children',
            'user',
            'departments',
            'provinces',
            'districts'
        ));
    }

    public function update(array $data, int $id)
    {
        $this->s_validation->validationUpdate($data);

        $tenant_data               = $this->s_repository->getTenantCompanyData($id);
        $tenant                    = $this->s_repository->findTenant($tenant_data->tenant_id);
        $data['modules']           = $this->s_repository->getModulesLandlord($data['module_id'] ?? []);
        $data['modules_childrens'] = $this->s_repository->getModulesChildrenLandlord($data['child_id'] ?? []);
        $data['files_route']       = $tenant_data->files_route;

        DB::connection('landlord')->beginTransaction();
        try {
            $dto_company_landlord = $this->s_dto->getDtoUpdateCompanyLandlord($data);
            $company_landlord     = $this->s_repository->updateCompanyLandlord($id, $dto_company_landlord);

            $dto_company_invoice_landlord = $this->s_dto->getDtoCompanyInvoiceLandlord($data, $company_landlord);
            $company_invoice_landlord     = $this->s_repository->updateCompanyInvoiceLandlord($tenant_data->company_invoice_id, $dto_company_invoice_landlord);

            DB::connection('landlord')->commit();
        } catch (Throwable $e) {
            DB::connection('landlord')->rollBack();
            throw $e;
        }

        $res = $this->updateDataTenant($tenant_data, $tenant, $data);

        $this->updateLogo($data, $tenant_data, $company_landlord, $tenant, $res->company_tenant);
        $this->updateCertificate($tenant_data, $data, $tenant, $company_invoice_landlord, $res->company_invoice_tenant);
    }

    public function updateDataTenant($tenant_data, Tenant $tenant, array $data)
    {
        $tenant->makeCurrent();

        DB::connection('tenant')->beginTransaction();
        try {
            //=========== MODULOS =============
            $this->s_repository->deleteTenantModulesGrandChildren($tenant_data->database);
            $this->s_repository->deleteTenantModulesChildren($tenant_data->database);
            $this->s_repository->deleteTenantModules($tenant_data->database);

            $dto_modules_tenant = $this->s_dto->getDtoModulesTenant($data['modules']);
            $this->s_repository->insertMasiveModulesTenant($dto_modules_tenant);

            $dto_childrens_tenant = $this->s_dto->getDtoModulesChildrenTenant($data['modules_childrens']);
            $this->s_repository->insertMasiveModulesChildrenTenant($dto_childrens_tenant);

            //=========== PLAN ==========
            $this->s_repository->deleteTenantPlans($tenant_data->database);
            $dto_plan_tenant = $this->s_dto->getDtoPlanTenant($data['plan_id']);
            $this->s_repository->storePlanTenant($dto_plan_tenant);

            //======== USER PASSWORD ==========
            $dto_user_tenant = $this->s_dto->getDtoUpdateUserTenant($data);
            $this->s_repository->updateUserAdminTenant($dto_user_tenant);

            //========= COMPANY ==========
            $dto_company_tenant = $this->s_dto->getDtoTenantCompanyUpdate($data);
            $company_tenant     = $this->s_repository->updateCompanyTenant($dto_company_tenant, 1);

            //============= COMPANY INVOICE TENANT ===========
            $dto_tenant_company_invoice = $this->s_dto->getDtoTenantCompanyInvoice($data, $company_tenant);
            $company_invoice_tenant     = $this->s_repository->updateCompanyInvoiceTenant($dto_tenant_company_invoice);

            DB::connection('tenant')->commit();

            Tenant::forgetCurrent();
            return (object) [
                'company_invoice_tenant' => $company_invoice_tenant,
                'company_tenant'         => $company_tenant,
            ];
        } catch (Throwable $th) {
            DB::connection('tenant')->rollBack();
            Tenant::forgetCurrent();
            throw $th;
        }
    }

    public function updateLogo(array $data, $tenant_data, Company $company_landlord, Tenant $tenant, TenantCompany $company_tenant)
    {
        $logo      = $data['logo'] ?? null;
        $logo_url  = null;
        $logo_name = null;

        if ($logo) {
            if ($tenant_data->logo_url) {
                UtilController::deleteFile($tenant_data->logo_url);
            }

            UtilController::saveFileFromLandlord($logo, 'logo_principal', $tenant_data->files_route . '/logo');

            $logo_name = 'logo_principal.' . $logo->getClientOriginalExtension();
            $logo_url  = 'storage/' . $tenant_data->files_route . '/logo/' . $logo_name;
        } elseif ($tenant_data->logo_url) {
            UtilController::deleteFile($tenant_data->logo_url);
        }

        $this->s_repository->saveLogoLandlord($company_landlord, $logo_url, $logo_name);

        $tenant->makeCurrent();
        $this->s_repository->saveLogoTenant($company_tenant, $logo_url, $logo_name);
        Tenant::forgetCurrent();
    }

    public function updateCertificate($tenant_data, array $data, Tenant $tenant, CompanyInvoice $company_invoice_landlord, TenantCompanyInvoice $company_invoice_tenant)
    {
        $certificate = $data['certificate'] ?? null;
        if ($certificate) {
            if ($tenant_data->certificate_url) {
                UtilController::deleteFile($tenant_data->certificate_url);
            }

            $this->saveCertificate($data, $tenant, $company_invoice_landlord, $company_invoice_tenant);
        }
    }

    public function blockAccount(array $data, int $id)
    {
        $tenant_data      = $this->s_repository->getTenantCompanyData($id);
        $tenant           = $this->s_repository->findTenant($tenant_data->tenant_id);
        $company_landlord = null;

        DB::connection('landlord')->beginTransaction();
        try {
            $company_landlord = $this->s_repository->blockAccountLandlord($id, $data['block_account']);

            DB::connection('landlord')->commit();
        } catch (Throwable $e) {
            DB::connection('landlord')->rollBack();
            throw $e;
        }

        $tenant->makeCurrent();
        DB::connection('tenant')->beginTransaction();
        try {
            $this->s_repository->blockAccountTenant($data['block_account']);
            DB::connection('tenant')->commit();

            Cache::forget("company_status_" . $tenant->id);

            Tenant::forgetCurrent();
        } catch (Throwable $e) {
            DB::connection('tenant')->rollBack();
            throw $e;
        }

        return $company_landlord;
    }
}
