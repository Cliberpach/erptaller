<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BancoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'banco_empresas';

    protected $guarded = [''];

    public function empresa() {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
