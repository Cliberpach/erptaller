<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSerialization extends Model
{
    use HasFactory;
    protected $table = 'document_serializations';

    protected $guarded = [''];
}
