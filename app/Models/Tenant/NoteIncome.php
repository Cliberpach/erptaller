<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteIncome extends Model
{
    use HasFactory;
    protected $table = 'notes_income';

    protected $fillable = [
        'user_recorder_id',
        'user_recorder_name',
        'observation'
    ];
}
