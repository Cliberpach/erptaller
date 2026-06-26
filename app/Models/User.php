<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [''];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Sedes asignadas al usuario (M:N), con marca de sede por defecto.
     * Multi-sede: en contexto tenant esta relación lee la BD del tenant
     * (App\Models\User usa la conexión default = 'tenant' en ese contexto).
     */
    public function sedes()
    {
        return $this->belongsToMany(\App\Models\Tenant\Sede::class, 'sede_user')
            ->withPivot('es_default')
            ->withTimestamps();
    }

    /**
     * Sede por defecto del usuario (pivot es_default = true).
     */
    public function sedeDefault()
    {
        return $this->sedes()->wherePivot('es_default', true)->first();
    }
}
