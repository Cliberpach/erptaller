<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInvoice extends Model
{
    use HasFactory;

    protected $connection = 'landlord';
    protected $table = 'company_invoice';
    protected $guarded = [''];
}
