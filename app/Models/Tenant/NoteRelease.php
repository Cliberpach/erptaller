<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteRelease extends Model
{
    use HasFactory;
    protected $table = 'notes_release';

    protected $guarded = [''];
}
