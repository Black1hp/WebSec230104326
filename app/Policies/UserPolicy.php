<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->role === 'admin';
    }

    public function view(User $user, User $model)
    {
        return $user->role === 'admin' || $user->id === $model->id;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function edit(User $user, User $model = null)
    {
        if ($model === null) {
            return $user->role === 'admin';
        }
        return $user->role === 'admin' || $user->id === $model->id;
    }

    public function update(User $user, User $model = null)
    {
        if ($model === null) {
            return $user->role === 'admin';
        }
        return $user->role === 'admin' || $user->id === $model->id;
    }

    public function delete(User $user, User $model)
    {
        return $user->role === 'admin' && $user->id !== $model->id;
    }
}

