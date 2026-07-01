<?php

namespace App\Http\Services\Landlord\Maintenance\Company;

use App\Models\Department;
use App\Models\District;
use App\Models\Landlord\Company;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Plan;
use App\Models\Province;
use App\Models\Tenant;
use App\Models\Tenant\Maintenance\Company\Company as TenantCompany;
use Illuminate\Support\Facades\Hash;

class CompanyDto
{
    public function getDtoTenant(array $data): array
    {
        $domain = strtolower($data['domain']);

        $domain = trim($domain);
        $domain = preg_replace('/\s+/', '', $domain);
        $domain = preg_replace('/[^a-z0-9\-\.]/', '', $domain);

        return [
            'domain' => $domain . "." . parse_url(config("app.url"), PHP_URL_HOST),
            'name'   => $data['razon_social']
        ];
    }

    public function getDtoCompanyLandlord(array $data, Tenant $tenant): array
    {
        $tenantDirectory = $data['domain'] . '_' . $tenant->id;

        return [
            'tenant_id'                 => $tenant->id,
            'ruc'                       => $data['ruc'],
            'business_name'             => $data['razon_social'],
            'abbreviated_business_name' => $data['razon_social_abreviada'],
            'fiscal_address'            => $data['direccion_fiscal'],
            'email'                     => $data['correo'],
            'plan'                      => $data['plan_id'],
            'files_route'               => $tenantDirectory,
            'token_placa'               => "nsHeEpNSOBr8ucEFnL7OtKmVkZhefUuvoM8O1Lz7uFEOi4KtFZ54==",
        ];
    }

    public function getDtoCompanyInvoiceLandlord(array $data, Company $company): array
    {
        $department = Department::findOrFail($data['department']);
        $province   = Province::findOrFail($data['province']);
        $district   = District::findOrFail($data['district']);

        return [
            'company_id'           => $company->id,
            'environment'          => 'BETA',
            'token_reniec'         => 'c36358c49922c564f035d4dc2ff3492fbcfd31ee561866960f75b79f7d645d7d',
            'plan'                 => 'FULL',
            'secondary_user'       => $data['secondary_user'],
            'secondary_password'   => $data['secondary_password'],
            'api_user_gre'         => $data['api_user_gre'],
            'api_password_gre'     => $data['api_pass_gre'],
            'certificate_password' => $data['certificate_password'],
            'ubigeo'               => $district->id,
            'department_id'        => $department->id,
            'province_id'          => $province->id,
            'district_id'          => $district->id,
            'department_name'      => $department->name,
            'province_name'        => $province->name,
            'district_name'        => $district->name,
        ];
    }

    public function getDtoTenantCompany(array $data): array
    {
        return [
            'ruc'                       => $data['ruc'],
            'business_name'             => $data['razon_social'],
            'abbreviated_business_name' => $data['razon_social_abreviada'],
            'domain'                    => $data['domain'],
            'files_route'               => $data['files_route'],
            'tenant_id'                 => $data['tenant_id'],
            'fiscal_address'            => $data['direccion_fiscal'],
            'email'                     => $data['correo'],
            'plan'                      => $data['plan_id'],
        ];
    }

    public function getDtoTenantCompanyUpdate(array $data): array
    {
        return [
            'ruc'                       => $data['ruc'],
            'business_name'             => $data['razon_social'],
            'abbreviated_business_name' => $data['razon_social_abreviada'],
            'fiscal_address'            => $data['direccion_fiscal'],
            'email'                     => $data['correo'],
            'plan'                      => $data['plan_id'],
        ];
    }

    public function getDtoTenantCompanyInvoice(array $data, TenantCompany $company): array
    {
        $department = Department::findOrFail($data['department']);
        $province   = Province::findOrFail($data['province']);
        $district   = District::findOrFail($data['district']);

        return [
            'company_id'           => $company->id,
            'plan'                 => $company->plan,
            'environment'          => 'BETA',
            'department_id'        => $department->id,
            'province_id'          => $province->id,
            'district_id'          => $district->id,
            'department_name'      => $department->name,
            'province_name'        => $province->name,
            'district_name'        => $district->name,
            'ubigeo'               => $district->id,
            'urbanization'         => 'PALERMO',
            'local_code'           => '0000',
            'certificate_password' => $data['certificate_password'],
            'secondary_user'       => $data['secondary_user'],
            'secondary_password'   => $data['secondary_password'],
            'api_user_gre'         => $data['api_user_gre'],
            'api_password_gre'     => $data['api_pass_gre'],
        ];
    }

    public function getDtoUserTenant(array $data, $collaborator_id): array
    {
        return [
            'name'             => 'ADMIN',
            'email'            => $data['correo'],
            'password'         => Hash::make($data['password']),
            'password_visible' => $data['password'],
            'collaborator_id'  => $collaborator_id
        ];
    }

    public function getDtoUpdateUserTenant(array $data): array
    {
        return [
            'email'            => $data['correo'],
            'password'         => Hash::make($data['password']),
            'password_visible' => $data['password'],
        ];
    }

    public function getDtoDocumentSerializationTenant(int $company_tenant_id): array
    {
        $serializable_document = GeneralTableDetail::on('landlord')
            ->where('symbol', 'NV')
            ->where('parameter', 'NV')
            ->first();

        return [
            'company_id'        => $company_tenant_id,
            'document_type_id'  => $serializable_document->id,
            'serie'             => $serializable_document->parameter . '01',
            'number_limit'      => 8,
            'destiny'           => 'NOTA DE VENTA',
            'default'           => 'NO',
            'final_number'      => 0,
            'description'       => $serializable_document->name,
        ];
    }

    public function getDtoModulesTenant($modules): array
    {
        $dto = [];

        foreach ($modules as $module) {
            $dto[] = [
                'id'          => $module->id,
                'description' => $module->description,
                'order'       => $module->order,
                'icon'        => $module->icon,
                'created_at'  => now(),
            ];
        }

        return $dto;
    }

    public function getDtoModulesChildrenTenant($childrens): array
    {
        $dto = [];

        foreach ($childrens as $child) {
            $dto[] = [
                'id'          => $child->id,
                'module_id'   => $child->module_id,
                'description' => $child->description,
                'route_name'  => $child->route_name,
                'order'       => $child->order,
                'created_at'  => now(),
            ];
        }

        return $dto;
    }

    public function getDtoPlanTenant(int $plan_id): array
    {
        $plan = Plan::on('landlord')->findOrFail($plan_id);

        return [
            'id'            => $plan->id,
            'description'   => $plan->description,
            'number_fields' => $plan->number_fields,
            'price'         => $plan->price,
        ];
    }

    public function getDtoUpdateCompanyLandlord(array $data): array
    {
        return [
            'ruc'                       => $data['ruc'],
            'business_name'             => $data['razon_social'],
            'abbreviated_business_name' => $data['razon_social_abreviada'],
            'fiscal_address'            => $data['direccion_fiscal'],
            'email'                     => $data['correo'],
            'plan'                      => $data['plan_id'],
        ];
    }
}
