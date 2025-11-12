<?php

namespace App\Http\Services\Tenant\Bookings\Booking;



class BookingManager
{
    protected BookingService $s_booking;

     public function __construct()
    {
        $this->s_booking    =   new BookingService();
    }

    public function store(array $data):array{
        return $this->s_booking->store($data);
    }


}
