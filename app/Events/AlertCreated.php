<?php

namespace App\Events;

use App\Models\Tenant\Alerts\Alert;
use App\Models\User; // O tu modelo de usuario
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AlertCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public Alert $alert;
    public User $user; 

    public function __construct(Alert $alert, User $user)
    {
        $this->alert = $alert;
        $this->user = $user;

        Log::info('🔔 Enviando AlertCreated', [
            'alert_id' => $alert->id,
            'name' => $alert->name,
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->user->id);
    }

    public function broadcastAs()
    {
        return 'alert.created';
    }

    public function broadcastWith()
    {
        return [
            'alert' => [
                'id' => $this->alert->id,
                'name' => $this->alert->name,
                'description' => $this->alert->description,
                'type_object' => $this->alert->type_object,
                'object_id' => $this->alert->object_id,
                'advance_date' => $this->alert->advance_date,
                'notice_date' => $this->alert->notice_date,
                'creator_user_name' => $this->alert->creator_user_name,
                'created_at' => $this->alert->created_at,
            ]
        ];
    }
}
