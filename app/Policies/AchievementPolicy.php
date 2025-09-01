<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Achievement;
use Illuminate\Auth\Access\HandlesAuthorization;

class AchievementPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        // Consider both role name variations; adjust if your roles differ
        return method_exists($user, 'hasRole') && ($user->hasRole('super_admin') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all; otherwise rely on explicit permission
        return $this->isAdmin($user) || $user->can('view_any_achievement');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Achievement $achievement): bool
    {
        if ($this->isAdmin($user) || $user->can('view_achievement')) {
            return true;
        }

        // Self-scope: owner can view own achievement
        if ($achievement->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins can create for anyone; others may create for themselves (self-scope)
        return $this->isAdmin($user) || $user->can('create_achievement') || $user->can('create_achievement_self');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Achievement $achievement): bool
    {
        if ($this->isAdmin($user) || $user->can('update_achievement')) {
            return true;
        }

        // Self can update only if owner AND created via self
        if ($achievement->user_id === $user->id) {
            // When self-scope permission exists, enforce it; otherwise default allow for self-created only
            $hasSelfPerm = $user->can('update_achievement_self');
            return ($hasSelfPerm || ! $user->can('update_achievement'))
                && ($achievement->created_via === 'self');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Achievement $achievement): bool
    {
        if ($this->isAdmin($user) || $user->can('delete_achievement')) {
            return true;
        }

        // Self can delete only if owner AND created via self
        if ($achievement->user_id === $user->id) {
            $hasSelfPerm = $user->can('delete_achievement_self');
            return ($hasSelfPerm || ! $user->can('delete_achievement'))
                && ($achievement->created_via === 'self');
        }

        return false;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('delete_any_achievement');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Achievement $achievement): bool
    {
        return $this->isAdmin($user) || $user->can('force_delete_achievement');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('force_delete_any_achievement');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Achievement $achievement): bool
    {
        return $this->isAdmin($user) || $user->can('restore_achievement');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('restore_any_achievement');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Achievement $achievement): bool
    {
        return $this->isAdmin($user) || $user->can('replicate_achievement');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $this->isAdmin($user) || $user->can('reorder_achievement');
    }
}
