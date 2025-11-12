<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'field_id' => $this->field_id,
        'customer_id' => new CustomerResource($this->customer), // Relación con Customer
        'schedule_id' => $this->schedule_id,
        'date' => $this->date,
        'total' => $this->total,
        'payment_status' => $this->payment_status,
        'status' => $this->status, 
        'booking_details' => BookingDetailResource::collection($this->bookingDetails), // Relación con BookingDetails
    ];
}

}
