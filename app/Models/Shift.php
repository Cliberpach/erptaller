<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Relación con PettyCashBook
    public function pettyCashBooks()
    {
        return $this->hasMany(PettyCashBook::class, 'shift_id');
    }
}
