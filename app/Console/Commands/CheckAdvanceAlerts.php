<?php

namespace App\Console\Commands;

use App\Events\AlertCreated;
use App\Models\Tenant\Alerts\Alert;
use App\Models\Tenant\Alerts\AlertUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckAdvanceAlerts extends Command
{
    protected $signature = 'alerts:check';
    protected $description = 'Verifica y notifica alertas activas según advance_date';

    public function handle()
    {
        $this->info('🔍 Verificando alertas activas...');

        $today = Carbon::today();

        // Alertas que ya deben mostrarse
        $alerts = Alert::where('status', 'ACTIVO')
            ->whereDate('advance_date', '<=', $today)
            ->get();

        if ($alerts->isEmpty()) {
            $this->info('✅ No hay alertas pendientes');
            return Command::SUCCESS;
        }

        $users = User::where('status', 'ACTIVO')->get();

        foreach ($alerts as $alert) {
            foreach ($users as $user) {

                $alertUser = AlertUser::where('alert_id', $alert->id)
                    ->where('user_id', $user->id)
                    ->first();

                // Si ya fue notificado → no emitir
                if ($alertUser && $alertUser->notified_at) {
                    continue;
                }

                event(new AlertCreated($alert, $user));

                $this->line("📢 Emitida alerta {$alert->id} → Usuario {$user->id}");
            }
        }

        $this->info('🎉 Proceso finalizado');
        return Command::SUCCESS;
    }
}
