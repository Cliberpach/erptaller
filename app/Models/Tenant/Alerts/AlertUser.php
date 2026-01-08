<?php

namespace App\Models\Tenant\Alerts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlertUser extends Model
{
    use HasFactory;

    protected $table = 'alert_user';
    protected $connection   = 'tenant';

    protected $fillable = [
        'alert_id',
        'user_id',
        'notified_at',
        'read_at',
        'dismissed_at',
        'action_taken',
        'notification_channel',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    /* =======================
     | Relaciones
     ======================= */

    public function alert()
    {
        return $this->belongsTo(Alert::class, 'alert_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /* =======================
     | Scopes útiles (PRO)
     ======================= */

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeNotNotified($query)
    {
        return $query->whereNull('notified_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
