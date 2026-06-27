<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'customer' && $booking->customer_id === $user->id)
            || ($user->role === 'mitra' && $booking->partner_id === $user->partnerProfile?->id);
    }
}
