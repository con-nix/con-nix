<?php

namespace App\Observers;

use App\Models\Organization;

class OrganizationObserver
{
    /**
     * Handle the Organization "created" event.
     */
    public function created(Organization $organization): void
    {
        $organization->owner->recordActivity(
            'organization_created',
            "Created organization {$organization->name}",
            $organization,
            [
                'organization_name' => $organization->name,
                'organization_slug' => $organization->slug,
            ]
        );
    }

    /**
     * Handle the Organization "updated" event.
     */
    public function updated(Organization $organization): void
    {
        if ($organization->wasChanged(['name', 'description'])) {
            $organization->owner->recordActivity(
                'organization_updated',
                "Updated organization {$organization->name}",
                $organization,
                [
                    'organization_name' => $organization->name,
                    'changes' => $organization->getChanges(),
                ]
            );
        }
    }

    /**
     * Handle the Organization "deleted" event.
     */
    public function deleted(Organization $organization): void
    {
        $organization->owner->recordActivity(
            'organization_deleted',
            "Deleted organization {$organization->name}",
            null, // Organization is deleted, so no subject
            [
                'organization_name' => $organization->name,
            ]
        );
    }

    /**
     * Handle the Organization "restored" event.
     */
    public function restored(Organization $organization): void
    {
        //
    }

    /**
     * Handle the Organization "force deleted" event.
     */
    public function forceDeleted(Organization $organization): void
    {
        //
    }
}
