<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetailBooking extends Model
{
    use HasFactory;

    protected $table = 'sales_documents_bookings';

    protected $guarded = [''];
}
