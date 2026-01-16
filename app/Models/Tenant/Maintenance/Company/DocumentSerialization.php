<?php

namespace App\Models\Tenant\Maintenance\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSerialization extends Model
{
    use HasFactory;

    protected $table = 'document_serializations';

    protected $connection = 'tenant';

    protected $guarded = [];

    protected $fillable = [
        'company_id',
        'document_type_id',
        'serie',
        'number_limit',
        'destiny',
        'default',
        'final_number',
        'description',
    ];
}
