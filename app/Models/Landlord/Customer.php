<?php

namespace App\Models\Landlord;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    protected $connection   = 'landlord';
    protected $table        = 'customers';

    protected $guarded = [''];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
