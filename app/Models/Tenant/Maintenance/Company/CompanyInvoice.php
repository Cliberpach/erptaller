<?php

namespace App\Models\Tenant\Maintenance\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInvoice extends Model
{
    use HasFactory;

    protected $table = 'company_invoices';
    protected $connection = 'tenant';
    protected $guarded = [''];
}
