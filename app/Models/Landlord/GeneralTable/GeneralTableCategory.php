<?php

namespace App\Models\Landlord\GeneralTable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralTableCategory extends Model
{
    use HasFactory;

    protected $table = 'general_table_categories';
    protected $connection = 'landlord';

    protected $fillable = [
        'general_table_id',
        'name',
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
     * Relación: Una categoría pertenece a un maestro.
     */
    public function master()
    {
        return $this->belongsTo(GeneralTable::class, 'general_table_id');
    }

    /**
     * Relación: Una categoría tiene muchos detalles.
     */
    public function details()
    {
        return $this->hasMany(GeneralTableDetail::class, 'category_id');
    }
}
