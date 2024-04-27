<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cinema;
use Illuminate\Auth\Access\HandlesAuthorization;

class CinemaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cinema');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cinema $cinema): bool
    {
        return $user->can('view_cinema');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_cinema');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cinema $cinema): bool
    {
        return $user->can('update_cinema');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cinema $cinema): bool
    {
        return $user->can('delete_cinema');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_cinema');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Cinema $cinema): bool
    {
        return $user->can('force_delete_cinema');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_cinema');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Cinema $cinema): bool
    {
        return $user->can('restore_cinema');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_cinema');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Cinema $cinema): bool
    {
        return $user->can('replicate_cinema');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_cinema');
    }
}
