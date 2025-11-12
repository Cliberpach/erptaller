<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationDocument extends Model
{
    use HasFactory;
    protected $table = 'reservation_documents';

    protected $guarded = [''];
}
