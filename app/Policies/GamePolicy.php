<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // Allow all users to see games listing
    }

    public function view(User $user, Game $game): bool
    {
        return true; // Allow all users to view individual games
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin'; // Only admins can create games
    }

    public function update(User $user, Game $game): bool
    {
        return $user->id === $game->user_id || $user->role === 'admin'; // Allow owner or admin to update
    }

    public function delete(User $user, Game $game): bool
    {
        return $user->role === 'admin'; // Only admins can delete
    }
}
