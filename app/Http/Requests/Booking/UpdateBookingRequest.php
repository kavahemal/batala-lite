<?php

namespace App\Http\Requests\Booking;

class UpdateBookingRequest extends StoreBookingRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('booking.edit', $this->route('booking'));
    }
}
