<?php

namespace App\Observers;

use App\Models\Repository;

class RepositoryObserver
{
    /**
     * Handle the Repository "created" event.
     */
    public function created(Repository $repository): void
    {
        $owner = $repository->getOwner();
        
        if ($owner instanceof \App\Models\User) {
            $owner->recordActivity(
                'repository_created',
                "Created repository {$repository->name}",
                $repository,
                [
                    'repository_name' => $repository->name,
                    'is_public' => $repository->is_public,
                ]
            );
        }
    }

    /**
     * Handle the Repository "updated" event.
     */
    public function updated(Repository $repository): void
    {
        $owner = $repository->getOwner();
        
        if ($owner instanceof \App\Models\User && $repository->wasChanged(['name', 'description', 'is_public'])) {
            $owner->recordActivity(
                'repository_updated',
                "Updated repository {$repository->name}",
                $repository,
                [
                    'repository_name' => $repository->name,
                    'changes' => $repository->getChanges(),
                ]
            );
        }
    }

    /**
     * Handle the Repository "deleted" event.
     */
    public function deleted(Repository $repository): void
    {
        $owner = $repository->getOwner();
        
        if ($owner instanceof \App\Models\User) {
            $owner->recordActivity(
                'repository_deleted',
                "Deleted repository {$repository->name}",
                null, // Repository is deleted, so no subject
                [
                    'repository_name' => $repository->name,
                ]
            );
        }
    }

    /**
     * Handle the Repository "restored" event.
     */
    public function restored(Repository $repository): void
    {
        //
    }

    /**
     * Handle the Repository "force deleted" event.
     */
    public function forceDeleted(Repository $repository): void
    {
        //
    }
}
