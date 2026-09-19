<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, User $record): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, User $record): bool
    {
        return (bool) $user->is_admin && ! $record->is_admin;
    }

    public function delete(User $user, User $record): bool
    {
        return false;
    }
}
