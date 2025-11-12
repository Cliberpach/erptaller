<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeIdentityDocument extends Model
{
    use HasFactory;
    protected $table = 'types_identity_documents';

    protected $guarded = [''];
}
