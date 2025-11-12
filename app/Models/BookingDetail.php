<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    protected $connection   = 'tenant';
    protected $table        = 'booking_detail';
    protected $fillable = [
        'booking_id','payment_type', 'payment', 'voucher','created_at'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
