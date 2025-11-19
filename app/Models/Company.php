<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $table = 'companies';
    protected $guarded = [''];

    protected $fillable = [
        'ruc', 'business_name', 'abbreviated_business_name', 'fiscal_address',
        'phone', 'cellphone', 'email', 'zip_code', 'facebook',
        'instagram', 'web', 'invoicing_status', 'logo','lat','lng','token_placa'
    ];

}
