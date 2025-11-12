<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    
    protected $connection   = 'landlord';
    protected $table        = 'companies';

    protected $guarded = [
        'document_number',
        'name',
        'phone',
        'status',
        'type_identity_document_id',
        'type_document_name',
        'type_document_abbreviation',
        'address',
        'email',
        'department_id',
        'province_id',
        'district_id',
        'department_name',
        'province_name',
        'district_name',
        'igv',
        'zone',
        'ubigeo'
    ];
}
