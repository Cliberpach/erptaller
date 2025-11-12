<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteReleaseDetail extends Model
{
    use HasFactory;
    protected $table = 'notes_release_detail';

    protected $guarded = [''];
}
