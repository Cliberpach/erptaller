<?php

namespace App\Console\Commands;

use App\Events\AlertCreated;
use App\Models\Tenant\Alerts\Alert;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckAdvanceAlerts extends Command
{
    protected $signature = 'alerts:check';
    protected $description = 'Verifica y notifica alertas cuya fecha anticipada es hoy';

    public function handle()
    {
        $this->info('🔍 Verificando alertas para hoy...');

        // Obtener alertas cuya advance_date es hoy
        $alerts = Alert::where('status', 'ACTIVO')
            ->whereDate('advance_date', Carbon::today())
            ->get();

        if ($alerts->isEmpty()) {
            $this->info('✅ No hay alertas para hoy');
            return 0;
        }

        $this->info("📢 Se encontraron {$alerts->count()} alertas");

        foreach ($alerts as $alert) {
            event(new AlertCreated($alert));

            $this->line("✅ Alerta enviada: {$alert->name}");
        }

        $this->info('🎉 Todas las alertas fueron enviadas');

        return 0;
    }
}
