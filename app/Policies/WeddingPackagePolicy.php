<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeddingPackage;

class WeddingPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, WeddingPackage $package): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, WeddingPackage $package): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, WeddingPackage $package): bool
    {
        return (bool) $user->is_admin && ! $package->bookings()->exists();
    }
}
