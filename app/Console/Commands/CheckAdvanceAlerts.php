<?php

namespace App\Console\Commands;

use App\Jobs\CheckTenantAlertsJob;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckAdvanceAlerts extends Command
{
    protected $signature = 'alerts:check';
    protected $description = 'Verifica y notifica alertas activas según advance_date';

    public function handle()
    {
        Log::channel('alerts')->info('🔍 Iniciando verificación de alertas');

        try {

            Tenant::chunk(50, function ($tenants) {
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
    }
}
