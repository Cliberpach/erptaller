<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCompany extends Model
{
    use HasFactory;
    protected $table = 'billing_companies';

    protected $guarded = [''];
}
