<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
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
    public function view(User $user, Organization $organization): bool
    {
        // If the organization is owned by the user or they are a member
        return $organization->owner_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create an organization
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        // Only the owner can update the organization
        return $organization->owner_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Only the owner can delete the organization
        return $organization->owner_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $this->delete($user, $organization);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organization $organization): bool
    {
        return $this->delete($user, $organization);
    }
}
