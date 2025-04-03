<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'employee']);
    }

    public function view(User $user, User $model)
    {
        // Admin can view any user profile
        if ($user->role === 'admin') {
            return true;
        }
        
        // Employee can view customer profiles and users with legacy 'user' role
        if ($user->role === 'employee' && ($model->role === 'customer' || $model->role === 'user')) {
            return true;
        }
        
        // Any user can view their own profile
        return $user->id === $model->id;
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
            return in_array($user->role, ['admin', 'employee']);
        }
        
        // Allow admin and employee to update credit for customer or user roles
        if (in_array($user->role, ['admin', 'employee']) && 
            in_array($model->role, ['customer', 'user'])) {
            return true;
        }
        
        // Allow user to update their own profile
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model)
    {
        return $user->role === 'admin' && $user->id !== $model->id;
    }
}
