<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSchedule extends Model
{
    use HasFactory;

    protected $connection   = 'tenant';
    protected $table        = 'bookings_schedules';

    protected $fillable = [
        'booking_id',
        'schedule_id',
        'description',
        'start_time',
        'end_time'
    ];

}
