<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('booking.index');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo('booking.index');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('booking.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Delivered bookings are locked: their stock has left the godown
        return $user->hasPermissionTo('booking.edit') && ! $booking->isLocked();
    }

    /**
     * Determine whether the user can mark the booking delivered (pending bookings only).
     */
    public function markDelivered(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo('booking.edit') && $booking->status === BookingStatus::PENDING;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->hasPermissionTo('booking.destroy') && ! $booking->isLocked();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }
}
