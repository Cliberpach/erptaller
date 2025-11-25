<?php

namespace App\Models\Landlord\GeneralTable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralTable extends Model
{
    use HasFactory;
    protected $table = 'general_tables';
    protected $connection = 'landlord';

    protected $fillable = [
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
        'create_user_name'
    ];

    public function details()
    {
        return $this->hasMany(GeneralTableDetail::class, 'general_table_id');
    }
}
