<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeIdentityDocument extends Model
{
    use HasFactory;
    protected $table = 'types_identity_documents';
    protected $connection = 'landlord';
    protected $guarded = [''];
}
