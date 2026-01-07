<?php

namespace App\Events;

use App\Models\Tenant\Alerts\Alert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AlertCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public Alert $alert;

    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
        Log::info('🔔 Enviando AlertCreated', [
            'alert_id' => $alert->id,
            'name' => $alert->name
        ]);
    }

    public function broadcastOn()
    {
        return new Channel('alerts');
    }

    public function broadcastAs()
    {
        return 'AlertCreated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->alert->id,
            'name' => $this->alert->name,
            'description' => $this->alert->description,
            'type_object' => $this->alert->type_object,
            'object_id' => $this->alert->object_id,
            'advance_date' => $this->alert->advance_date,
            'notice_date' => $this->alert->notice_date,
            'creator_user_name' => $this->alert->creator_user_name,
            'created_at' => $this->alert->created_at,
        ];
    }
}
