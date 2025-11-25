<?php

namespace App\Models\Landlord\GeneralTable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralTableDetail extends Model
{
    use HasFactory;

    protected $table = 'general_table_details';
    protected $connection = 'landlord';

    protected $fillable = [
        'general_table_id',
        'category_id',
        'name',
        'description',
        'symbol',
        'parameter',
        'status',
        'editable',
        'creator_user_id',
        'editor_user_id',
        'delete_user_id',
        'delete_user_name',
        'editor_user_name',
        'create_user_name',
    ];

    /**
     * Relación: el detalle pertenece al maestro.
     */
    public function master()
    {
        return $this->belongsTo(GeneralTable::class, 'general_table_id');
    }

    /**
     * Relación: el detalle pertenece a una categoría.
     */
    public function category()
    {
        return $this->belongsTo(GeneralTableCategory::class, 'category_id');
    }
}
