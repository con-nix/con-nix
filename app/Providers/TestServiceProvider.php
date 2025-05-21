<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (app()->environment('testing')) {
            \Illuminate\Support\Facades\Blade::directive('vite', function ($expression) {
                return '<!-- Vite assets would load here in production -->';
            });

            \Illuminate\Support\Facades\Blade::directive('fluxAppearance', function () {
                return '<!-- Flux appearance styles would load here in production -->';
            });
        }
    }
}
