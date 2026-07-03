<?php

namespace App\Http\Services\Tenant\Maintenance;

use App\Http\Services\Tenant\Cash\CajaService;
use App\Models\Landlord\Customer;
use App\Models\Tenant as LandlordTenant;
use App\Models\Tenant\Maintenance\Collaborator\Collaborator;
use App\Models\Tenant\Sede;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use Database\Seeders\landlord\PermissionSeeder;
use Database\Seeders\landlord\RoleSeeder;
use Database\Seeders\tenant\BrandSeeder;
use Database\Seeders\tenant\CategorySeeder;
use Database\Seeders\tenant\ConfigurationSeeder;
use Database\Seeders\tenant\PaymentConditionSeeder;
use Database\Seeders\tenant\PaymentMethodSeeder;
use Database\Seeders\tenant\PositionSeeder;
use Database\Seeders\tenant\ProofPaymentSeeder;
use Database\Seeders\tenant\ShiftSeeder;
use Database\Seeders\tenant\SupplierSeeder;
use Database\Seeders\tenant\TenantModuleSeeder;
use Database\Seeders\tenant\TypeFieldSeeder;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

/**
 * Herramientas destructivas de "Mantenimiento > Configuración" (card Herramientas de
 * Administración). Opera SIEMPRE sobre la conexión 'tenant' (la BD del tenant actual),
 * nunca sobre landlord.
 */
class TenantResetService
{
    /**
     * Tablas transaccionales (documentos de venta/compra/OT y derivados) a truncar en
     * "Eliminar documentos", conservando catálogo, productos y clientes. Orden: detalle
     * antes que cabecera (por si alguna FK no tiene ON DELETE CASCADE).
     */
    private const DOCUMENT_TABLES = [
        'sales_document_payments',
        'sales_documents_bookings',
        'sales_documents_services',
        'sales_documents_details',
        'sales_documents',
        'credit_notes_details',
        'credit_notes',
        'reservation_documents_detail',
        'reservation_documents',
        'notes_release_detail',
        'notes_release',
        'notes_income_detail',
        'notes_income',
        'quotes_products',
        'quotes_services',
        'quotes',
        'customer_accounts_details',
        'customer_accounts',
        'credits',
        'booking_detail',
        'bookings_schedules',
        'bookings',
        'kardex',
        'work_orders_services',
        'work_orders_products',
        'work_orders_technicians',
        'work_orders_images',
        'work_orders_inventory',
        'work_orders',
        'purchase_documents_detail',
        'purchase_documents',
    ];

    /** Tablas de sistema que NUNCA se truncan en "Eliminar TODO" (no son datos de negocio). */
    private const SYSTEM_TABLES = [
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'personal_access_tokens',
    ];

    /**
     * Tablas de identidad de la empresa que se crean UNA sola vez al aprovisionar el tenant
     * (CompanyService::insertDataTenant) y que NINGÚN seeder vuelve a sembrar: RUC/razón
     * social/IGV/files_route (companies), certificado y credenciales SOL de Greenter
     * (company_invoices), series/correlativo de "NV" (document_serializations, depende de un
     * lookup a landlord por symbol). Si se truncan en "Eliminar TODO" el tenant queda inservible
     * (sin RUC, sin certificado, sin numeración) y nada las reconstruye — se preservan intactas.
     */
    private const PROVISIONING_TABLES = [
        'companies',
        'company_invoices',
        'document_serializations',
    ];

    /** Mismo orden que DatabaseSeeder::runTenantSpecificSeeders() — mantener sincronizado. */
    private const TENANT_SEEDERS = [
        PositionSeeder::class,
        ConfigurationSeeder::class,
        PaymentMethodSeeder::class,
        BrandSeeder::class,
        CategorySeeder::class,
        ShiftSeeder::class,
        TenantModuleSeeder::class,
        PermissionSeeder::class,
        RoleSeeder::class,
        SupplierSeeder::class,
        ProofPaymentSeeder::class,
        TypeFieldSeeder::class,
        PaymentConditionSeeder::class,
    ];

    public function backup()
    {
        $connection = config('database.connections.tenant');
        $database   = $connection['database'];
        $host       = $connection['host'];
        $port       = $connection['port'] ?? 3306;
        $username   = $connection['username'];
        $password   = $connection['password'];

        $filename = 'backup_' . $database . '_' . now()->format('Y-m-d_His') . '.sql.gz';

        return response()->streamDownload(function () use ($database, $host, $port, $username, $password) {
            $cmd = sprintf(
                'mysqldump --no-tablespaces -h%s -P%s -u%s %s %s',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username),
                $password !== '' ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database)
            );

            $handle = popen($cmd, 'r');
            if (!$handle) {
                throw new Exception('NO SE PUDO INICIAR mysqldump');
            }

            $gz = gzopen('php://output', 'w9');
            while (!feof($handle)) {
                gzwrite($gz, fread($handle, 8192));
            }
            pclose($handle);
            gzclose($gz);
        }, $filename);
    }

    /** Trunca documentos de venta/compra/OT, conserva catálogo/productos/clientes. Congela stock. */
    public function limpiarDocumentos(): void
    {
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach (self::DOCUMENT_TABLES as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            // Correlativos de series: evita numeración salteada en el próximo comprobante.
            DB::table('document_serializations')->update(['final_number' => 0]);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();

            $this->log('LIMPIAR DOCUMENTOS (conserva catálogo/productos/clientes)');
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Trunca TODA la BD del tenant y re-siembra desde cero (mismos seeders que un tenant
     * nuevo) + sede principal/almacén/caja/admin placeholder, para dejar un tenant usable.
     *
     * NO recrea los 2 colaboradores demo (ventas/técnico) que sí se crean al registrar una
     * empresa nueva — el usuario deberá darlos de alta manualmente tras el reseteo.
     */
    public function limpiarTodo(): void
    {
        DB::beginTransaction();
        try {
            $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            $key    = 'Tables_in_' . config('database.connections.tenant.database');

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $row) {
                $table = $row->$key;
                if (in_array($table, self::SYSTEM_TABLES, true) || in_array($table, self::PROVISIONING_TABLES, true)) {
                    continue;
                }
                DB::table($table)->truncate();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            foreach (self::TENANT_SEEDERS as $seederClass) {
                $seeder = app($seederClass);
                $seeder->setContainer(app());
                app()->call([$seeder, 'run']);
            }

            $this->seedPlaceholderAdmin();
            $this->seedSedePrincipal();

            DB::commit();

            $this->log('ELIMINAR TODO (reset completo + reseed)');
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::rollBack();
            throw $e;
        }
    }

    private function seedPlaceholderAdmin(): void
    {
        $collaborator                              = new Collaborator();
        $collaborator->full_name                   = 'ADMIN';
        $collaborator->document_type_id            = 1;
        $collaborator->document_number             = '77412431';
        $collaborator->address                      = 'AV HUSARES 123';
        $collaborator->phone                       = '999999999';
        $collaborator->work_days                    = 30;
        $collaborator->rest_days                    = 20;
        $collaborator->monthly_salary               = 9999;
        $collaborator->daily_salary                 = 100;
        $collaborator->position_id                  = 1;
        $collaborator->document_type_abbreviation   = 'DNI';
        $collaborator->save();

        $user                   = new User();
        $user->name             = 'ADMIN';
        $user->email            = 'admin@gmail.com';
        $user->password         = Hash::make('123456789');
        $user->password_visible = '123456789';
        $user->collaborator_id  = $collaborator->id;
        $user->save();

        $role = Role::where('name', 'admin')->first();
        if ($role) {
            $user->assignRole($role);
        }
    }

    private function seedSedePrincipal(): void
    {
        $sede = Sede::create([
            'numero'       => 1,
            'nombre'       => 'SEDE PRINCIPAL',
            'codigo'       => 'S001',
            'es_principal' => true,
            'direccion'    => 'AV HUSARES 123',
            'ubigeo'       => null,
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

        $admin = User::where('email', 'admin@gmail.com')->first();
        if ($admin) {
            $admin->sedes()->attach($sede->id, ['es_default' => true]);
        }

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

    private function log(string $accion): void
    {
        Log::info('HERRAMIENTAS DE ADMINISTRACIÓN: ' . $accion, [
            'tenant_id' => LandlordTenant::current()?->id,
            'usuario'   => auth()->user()?->email,
            'ip'        => request()->ip(),
            'fecha'     => now()->toDateTimeString(),
        ]);
    }
}
