<?php

namespace App\Policies;

use App\Models\Repository;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RepositoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Repository $repository): bool
    {
        // Public repositories can be viewed by anyone
        if ($repository->is_public) {
            return true;
        }

        // Check if the repository belongs to the user
        if ($repository->user_id === $user->id) {
            return true;
        }

        // Check if the repository belongs to an organization owned by the user
        if ($repository->organization_id && 
            $repository->organization->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Repository $repository): bool
    {
        // Check if the repository belongs to the user
        if ($repository->user_id === $user->id) {
            return true;
        }

        // Check if the repository belongs to an organization owned by the user
        if ($repository->organization_id && 
            $repository->organization->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Repository $repository): bool
    {
        // Check if the repository belongs to the user
        if ($repository->user_id === $user->id) {
            return true;
        }

        // Check if the repository belongs to an organization owned by the user
        if ($repository->organization_id && 
            $repository->organization->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Repository $repository): bool
    {
        return $this->delete($user, $repository);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Repository $repository): bool
    {
        return $this->delete($user, $repository);
    }
}
