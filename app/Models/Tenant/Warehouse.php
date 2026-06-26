<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';

    protected $guarded = [''];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    /**
     * Sede a la que pertenece el almacén (Multi-Sede Etapa 3a).
     */
    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
