<?php

namespace App\Providers;

use App\Models\Organization;
use App\Models\Repository;
use App\Observers\OrganizationObserver;
use App\Observers\RepositoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Repository::observe(RepositoryObserver::class);
        Organization::observe(OrganizationObserver::class);
    }
}
