<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Booking $booking): bool
    {
        return (bool) $user->is_admin || $booking->user_id === $user->id;
    }

    public function update(User $user, Booking $booking): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }
}
