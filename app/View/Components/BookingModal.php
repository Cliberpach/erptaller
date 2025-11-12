<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BookingModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $hour;
    public $hourid;
    public $today;
    public $field;

    public function __construct($hour, $hourid, $today, $field)
    {
        $this->hour = $hour;
        $this->hourid = $hourid;
        $this->today = $today;
        $this->field = $field;
    }

    public function render()
    {
        return view('booking.booking-modal');
    }
}
