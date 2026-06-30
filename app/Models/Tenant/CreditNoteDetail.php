<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Detalle de la Nota de Crédito (tabla credit_notes_details).
 * PK COMPUESTA (credit_note_id, product_id) -> $incrementing=false. La inserción se
 * hace vía DB::table en el service (un producto por NC: el espejo agrupa por product_id).
 */
class CreditNoteDetail extends Model
{
    protected $table = 'credit_notes_details';
    protected $guarded = [];
    public $incrementing = false;

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }
}
