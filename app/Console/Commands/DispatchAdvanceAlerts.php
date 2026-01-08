<?php

namespace App\Console\Commands;

use App\Jobs\CheckTenantAlertsJob;
use Illuminate\Console\Command;
use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchAdvanceAlerts extends Command
{
    protected $signature = 'alerts:dispatch';
    protected $description = 'Despacha jobs de alertas por tenant';

    public function handle()
    {
        try {

            Tenant::where('status', 'ACTIVO')
                ->chunk(50, function ($tenants) {
                    foreach ($tenants as $tenant) {
                        dispatch(new CheckTenantAlertsJob($tenant->id));
                    }
                });

            Log::channel('alerts')->info('🎉 Proceso finalizado');

            return Command::SUCCESS;
        } catch (Throwable $e) {

            // 🔥 LOG COMPLETO DEL ERROR
            Log::channel('alerts')->error('❌ ERROR EN alerts:check', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // Mostrar también en consola
            $this->error('❌ Error ejecutando alerts:check');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->info('🚀 Jobs de alertas despachados');
    }
}
