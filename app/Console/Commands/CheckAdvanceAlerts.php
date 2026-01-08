<?php

namespace App\Console\Commands;

use App\Events\AlertCreated;
use App\Models\Tenant\Alerts\Alert;
use App\Models\Tenant\Alerts\AlertUser;
use App\Models\Tenant\User;
use Carbon\Carbon;
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
            $today = Carbon::today();

            $alerts = Alert::where('status', 'ACTIVO')
                ->whereDate('advance_date', '<=', $today)
                ->get();

            if ($alerts->isEmpty()) {
                Log::channel('alerts')->info('✅ No hay alertas pendientes');
                return Command::SUCCESS;
            }

            $users = User::where('status', 'ACTIVO')->get();

            foreach ($alerts as $alert) {
                foreach ($users as $user) {

                    $alertUser = AlertUser::where('alert_id', $alert->id)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($alertUser && $alertUser->notified_at) {
                        Log::channel('alerts')->info('⏭️ Omitida (ya notificada)', [
                            'alert_id' => $alert->id,
                            'user_id'  => $user->id,
                        ]);
                        continue;
                    }

                    event(new AlertCreated($alert, $user));

                    Log::channel('alerts')->info('📢 Alerta emitida', [
                        'alert_id' => $alert->id,
                        'user_id'  => $user->id,
                    ]);
                }
            }

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
