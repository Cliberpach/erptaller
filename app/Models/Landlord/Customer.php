<?php

namespace App\Models\Landlord;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    // customers se movió a la BD del TENANT (clientes privados por empresa).
    // Nota: el modelo sigue en namespace Landlord por compatibilidad; renombrar a Tenant\Customer es follow-up.
    protected $connection   = 'tenant';
    protected $table        = 'customers';

    protected $guarded = [''];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
