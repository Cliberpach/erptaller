<?php

namespace App\Models\Tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'sedes';

    protected $fillable = [
        'numero',
        'nombre',
        'codigo',
        'es_principal',
        'direccion',
        'telefono',
        'ubigeo',
        'status',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'numero'       => 'integer',
    ];

    /**
     * Usuarios asignados a la sede (M:N), con marca de sede por defecto.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'sede_user')
            ->withPivot('es_default')
            ->withTimestamps();
    }

    /**
     * Almacenes de la sede (Multi-Sede Etapa 3a).
     */
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'sede_id');
    }

    /**
     * Scope: la sede principal (matriz).
     */
    public function scopePrincipal($query)
    {
        return $query->where('es_principal', true);
    }
}
