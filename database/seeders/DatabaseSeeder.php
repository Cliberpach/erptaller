<?php

namespace Database\Seeders;

use Database\Seeders\landlord\BankSeeder;
use Database\Seeders\landlord\BrandSeeder as LandlordBrandSeeder;
use Illuminate\Database\Seeder;
use Spatie\Multitenancy\Models\Tenant;
use Database\Seeders\landlord\PermissionSeeder;
use Database\Seeders\landlord\PlanSeeder;
use Database\Seeders\landlord\RoleSeeder;
use Database\Seeders\landlord\UserSeeder;
use Database\Seeders\landlord\BrandSeeder;
use Database\Seeders\landlord\CustomerSeeder;
use Database\Seeders\landlord\GeneralTableSeeder;
use Database\Seeders\landlord\IdentityDocumentSeeder;
use Database\Seeders\landlord\InvoiceTypeSeeder;
use Database\Seeders\landlord\ModelSeeder;
use Database\Seeders\landlord\ModuleSeeder;
use Database\Seeders\landlord\TypeIdentityDocumentSeeder;
use Database\Seeders\landlord\YearSeeder;
use Database\Seeders\tenant\CategorySeeder;
use Database\Seeders\tenant\ConfigurationSeeder;
use Database\Seeders\tenant\DepartmentSeeder;
use Database\Seeders\tenant\DistrictSeeder;
use Database\Seeders\tenant\DocumentTypeSeeder;
use Database\Seeders\tenant\PaymentMethodSeeder;
use Database\Seeders\tenant\PettyCashSeeder;
use Database\Seeders\tenant\PositionSeeder;
use Database\Seeders\tenant\ProofPaymentSeeder;
use Database\Seeders\tenant\ProvinceSeeder;
use Database\Seeders\tenant\ShiftSeeder;
use Database\Seeders\tenant\SupplierSeeder;
use Database\Seeders\tenant\TypeFieldSeeder;
use Database\Seeders\tenant\UserSeeder as TenantUserSeeder;
use Database\Seeders\tenant\WarehouseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Tenant::checkCurrent() ? $this->runTenantSpecificSeeders() : $this->runLandlordSpecificSeeders();
    }

    public function runTenantSpecificSeeders()
    {
        // example: $this->call(TipoDocumentoSeeder::class);
        // note: no olvida llamar al seeder en use part
        $this->call(PositionSeeder::class);
        $this->call(ConfigurationSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        $this->call(WarehouseSeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(DocumentTypeSeeder::class);
        $this->call(PettyCashSeeder::class);
        $this->call(ShiftSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(ProvinceSeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(ProofPaymentSeeder::class);
        $this->call(TypeFieldSeeder::class);

        $this->call(TenantUserSeeder::class);
    }

    public function runLandlordSpecificSeeders()
    {
        // example: $this->call(ModuleSeeder::class);
        // note: no olvida llamar al seeder en use part
        $this->call(ModuleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(YearSeeder::class);
        $this->call(LandlordBrandSeeder::class);
        $this->call(ModelSeeder::class);
        $this->call(GeneralTableSeeder::class);
        $this->call(IdentityDocumentSeeder::class);
        $this->call(TypeIdentityDocumentSeeder::class);
        $this->call(InvoiceTypeSeeder::class);
        $this->call(BankSeeder::class);
    }
}
