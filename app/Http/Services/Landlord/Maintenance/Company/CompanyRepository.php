<?php

namespace App\Http\Services\Landlord\Maintenance\Company;

use App\Models\Landlord\Company;
use App\Models\Landlord\CompanyInvoice;
use App\Models\Module;
use App\Models\ModuleChild;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Tenant\Maintenance\Collaborator\Collaborator;
use App\Models\Tenant\Maintenance\Company\Company as TenantCompany;
use App\Models\Tenant\Maintenance\Company\CompanyInvoice as TenantCompanyInvoice;
use App\Models\Tenant\Maintenance\Company\Module as TenantModule;
use App\Models\Tenant\Maintenance\Company\ModuleChild as TenantModuleChild;
use App\Models\Tenant\Maintenance\Company\Plan as TenantPlan;
use App\Models\Tenant\DocumentSerialization;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CompanyRepository
{
    public function storeTenant(array $dto): Tenant
    {
        return Tenant::create($dto);
    }

    public function storeCompanyLandlord(array $dto): Company
    {
        return Company::create($dto);
    }

    public function updateCompanyLandlord(int $id, array $dto): Company
    {
        $company = Company::findOrFail($id);
        $company->update($dto);
        return $company;
    }

    public function storeCompanyInvoiceLandlord(array $dto): CompanyInvoice
    {
        return CompanyInvoice::create($dto);
    }

    public function getModulesLandlord(array $modules)
    {
        return Module::on('landlord')->whereIn('id', $modules)->get();
    }

    public function getModulesChildrenLandlord(array $childrens)
    {
        return ModuleChild::on('landlord')->whereIn('id', $childrens)->get();
    }

    public function findPlanLandlord(int $id): Plan
    {
        return Plan::findOrFail($id);
    }

    public function storeCompanyTenant(array $dto): TenantCompany
    {
        return TenantCompany::create($dto);
    }

    public function updateCompanyTenant(array $dto, int $company_tenant_id): TenantCompany
    {
        $tenant_company = TenantCompany::findOrFail($company_tenant_id);
        $tenant_company->update($dto);
        return $tenant_company;
    }

    public function storeCompanyInvoiceTenant(array $dto): TenantCompanyInvoice
    {
        return TenantCompanyInvoice::create($dto);
    }

    public function updateCompanyInvoiceTenant(array $dto): TenantCompanyInvoice
    {
        $instance = TenantCompanyInvoice::findOrFail(1);
        $instance->update($dto);
        return $instance;
    }

    public function getCollaboratorAdminTenant(): Collaborator
    {
        return Collaborator::findOrFail(1);
    }

    public function updateUserAdminTenant(array $dto): User
    {
        $user = User::findOrFail(1);
        $user->update($dto);
        return $user;
    }

    public function assignRoleAdmin(User $user): void
    {
        $role = Role::where('name', 'admin')->first();
        $user->assignRole($role);
    }

    public function storeDocumentSerializationTenant(array $dto): DocumentSerialization
    {
        return DocumentSerialization::create($dto);
    }

    public function insertMasiveModulesTenant(array $dto): void
    {
        TenantModule::insert($dto);
    }

    public function insertMasiveModulesChildrenTenant(array $dto): void
    {
        TenantModuleChild::insert($dto);
    }

    public function storePlanTenant(array $dto): TenantPlan
    {
        return TenantPlan::create($dto);
    }

    public function saveLogoLandlord(Company $company, ?string $logo_url, ?string $logo_name): void
    {
        $company->logo_url = $logo_url;
        $company->logo     = $logo_name;
        $company->saveQuietly();
    }

    public function saveLogoTenant(TenantCompany $company, ?string $logo_url, ?string $logo_name): void
    {
        $company->logo_url = $logo_url;
        $company->logo     = $logo_name;
        $company->saveQuietly();
    }

    public function saveCertLandlord(CompanyInvoice $company_invoice, string $url, string $name): void
    {
        $company_invoice->certificate     = $name;
        $company_invoice->certificate_url = $url;
        $company_invoice->saveQuietly();
    }

    public function saveCertTenant(TenantCompanyInvoice $company_invoice, string $url, string $name): void
    {
        $company_invoice->certificate     = $name;
        $company_invoice->certificate_url = $url;
        $company_invoice->saveQuietly();
    }

    public function getAllModules()
    {
        return Module::with('children.grandchildren')->get();
    }

    public function getTenantCompanyData(?int $company_id)
    {
        $tenant_data = DB::table('tenants AS t')
            ->join('companies AS c', 'c.tenant_id', 't.id')
            ->leftJoin('company_invoice AS ci', 'ci.company_id', 'c.id')
            ->select(
                't.database',
                't.domain',
                't.id as tenant_id',

                'c.id',
                'c.ruc',
                'c.business_name',
                'c.abbreviated_business_name',
                'c.fiscal_address',
                'c.plan',
                'c.logo_url',
                'c.files_route',

                'ci.id as company_invoice_id',
                'ci.department_id',
                'ci.province_id',
                'ci.district_id',
                'ci.secondary_user',
                'ci.secondary_password',
                'ci.api_user_gre',
                'ci.api_password_gre',
                'ci.certificate_password',
                'ci.certificate',
                'ci.certificate_url'
            );

        if ($company_id) {
            $tenant_data->where('c.id', $company_id);
        }

        return $tenant_data->first();
    }

    public function getTenantModules(string $database)
    {
        return DB::table("$database.modules AS m")->select('m.id')->get();
    }

    public function getTenantModulesChildren(string $database)
    {
        return DB::table("$database.module_children AS mc")->select('mc.id')->get();
    }

    public function getTenantModulesGrandChildren(string $database)
    {
        return DB::table("$database.module_grand_children AS mgc")->select('mgc.id')->get();
    }

    public function getTenantUser(string $database)
    {
        return DB::table("$database.users as u")->select('u.*')->where('u.id', 1)->first();
    }

    public function getPlans()
    {
        return Plan::all();
    }

    public function deleteTenantModules($database)
    {
        DB::table("$database.modules")->delete();
    }

    public function deleteTenantModulesChildren($database)
    {
        DB::table("$database.module_children")->delete();
    }

    public function deleteTenantModulesGrandChildren($database)
    {
        DB::table("$database.module_grand_children")->delete();
    }

    public function deleteTenantPlans($database)
    {
        DB::table("$database.plans")->delete();
    }

    public function findTenant(int $tenant_id): Tenant
    {
        return Tenant::findOrFail($tenant_id);
    }

    public function updateCompanyInvoiceLandlord(int $company_invoice_id, array $dto)
    {
        $company_invoice = CompanyInvoice::findOrFail($company_invoice_id);
        $company_invoice->update($dto);
        return $company_invoice;
    }

    public function blockAccountLandlord(int $company_id, int $status): Company
    {
        $company_landlord                = Company::findOrFail($company_id);
        $company_landlord->block_account = $status;
        $company_landlord->save();

        return $company_landlord;
    }

    public function blockAccountTenant(int $status): TenantCompany
    {
        $company_landlord                = TenantCompany::findOrFail(1);
        $company_landlord->block_account = $status;
        $company_landlord->save();

        return $company_landlord;
    }
}
