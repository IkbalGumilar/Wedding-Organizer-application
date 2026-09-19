<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Gallery $gallery): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Gallery $gallery): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Gallery $gallery): bool
    {
        return (bool) $user->is_admin;
    }
}
