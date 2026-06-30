<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Nota de Crédito (cabecera) sobre la tabla credit_notes existente.
 * Capa 1: solo documento + PDF. SUNAT/stock/caja en capas siguientes (sunat_status='PENDIENTE').
 */
class CreditNote extends Model
{
    protected $table = 'credit_notes';
    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(CreditNoteDetail::class, 'credit_note_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}
