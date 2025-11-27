<?php

namespace Database\Seeders;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\DocumentSerialization;
use Database\Seeders\Landlord\BrandSeeder as LandlordBrandSeeder;
use Illuminate\Database\Seeder;
use Spatie\Multitenancy\Models\Tenant;
use Database\Seeders\Landlord\ModuleSeeder;
use Database\Seeders\Landlord\PermissionSeeder;
use Database\Seeders\Landlord\PlanSeeder;
use Database\Seeders\Landlord\RoleSeeder;
use Database\Seeders\Landlord\UserSeeder;
use Database\Seeders\Tenant\BankSeeder;
use Database\Seeders\Tenant\BrandSeeder;
use Database\Seeders\Tenant\CategorySeeder;
use Database\Seeders\Landlord\CustomerSeeder;
use Database\Seeders\Landlord\GeneralTableSeeder;
use Database\Seeders\Landlord\IdentityDocumentSeeder;
use Database\Seeders\Landlord\ModelSeeder;
use Database\Seeders\Landlord\YearSeeder;
use Database\Seeders\Tenant\ConfigurationSeeder;
use Database\Seeders\Tenant\DepartmentSeeder;
use Database\Seeders\Tenant\DistrictSeeder;
use Database\Seeders\Tenant\DocumentTypeSeeder;
use Database\Seeders\Tenant\PaymentMethodSeeder;
use Database\Seeders\Tenant\PettyCashSeeder;
use Database\Seeders\Tenant\PositionSeeder;
use Database\Seeders\Tenant\ProofPaymentSeeder;
use Database\Seeders\Tenant\ProvinceSeeder;
use Database\Seeders\Tenant\ShiftSeeder;
use Database\Seeders\Tenant\SupplierSeeder;
use Database\Seeders\Tenant\TypeFieldSeeder;
use Database\Seeders\Tenant\TypeIdentityDocumentSeeder;
use Database\Seeders\Tenant\WarehouseSeeder;

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
        $this->call(GeneralTableSeeder::class);
        $this->call(IdentityDocumentSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        $this->call(WarehouseSeeder::class);
        $this->call(TypeIdentityDocumentSeeder::class);
        $this->call(BankSeeder::class);
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

        $this->call(UserSeeder::class);
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
    }
}
