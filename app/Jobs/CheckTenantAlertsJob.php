<?php

namespace App\Jobs;

use App\Events\AlertCreated;
use App\Models\Tenant\Alerts\Alert;
use App\Models\Tenant\Alerts\AlertUser;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckTenantAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $timeout = 60;
    public int $tries = 3;
    /**
     * Create a new job instance.
     */
    public function __construct(public int $tenantId) {}


    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $tenant = Tenant::findOrFail($this->tenantId);
            $tenant->makeCurrent();

            Log::channel('alerts')->info('🏢 Tenant activo', [
                'tenant_id' => $tenant->id,
                'db' => config('database.connections.tenant.database')
            ]);

            $today = Carbon::today();

            $alerts = Alert::where('status', 'ACTIVO')
                ->whereDate('advance_date', '<=', $today)
                ->get();

            foreach ($alerts as $alert) {
                foreach (User::where('status', 'ACTIVO')->get() as $user) {

                    $alertUser = AlertUser::firstOrCreate([
                        'alert_id' => $alert->id,
                        'user_id' => $user->id,
                    ]);

                    if ($alertUser->notified_at) {
                        continue;
                    }

                    event(new AlertCreated($alert, $user));

                    $alertUser->update([
                        'notified_at' => now(),
                    ]);
                }
            }

            $tenant->forgetCurrent();
        } catch (Throwable $e) {

            Log::channel('alerts')->error('❌ Error en CheckTenantAlertsJob', [
                'tenant_id' => $this->tenantId,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            throw $e; 
        }
    }
}
