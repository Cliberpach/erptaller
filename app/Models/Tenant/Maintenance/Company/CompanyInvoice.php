<?php

namespace App\Models\Tenant\Maintenance\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInvoice extends Model
{
    use HasFactory;

    protected $table = 'company_invoices';
    protected $connection = 'tenant';
    protected $fillable = [
        'company_id',
        'plan',
        'environment',
        'department_id',
        'province_id',
        'district_id',
        'department_name',
        'province_name',
        'district_name',
        'ubigeo',
        'urbanization',
        'local_code',
        'secondary_user',
        'secondary_password',
        'api_user_gre',
        'api_password_gre',
    ];
}
