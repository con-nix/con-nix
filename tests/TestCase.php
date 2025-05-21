<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Vite;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable vite for all tests
        $this->disableViteForTests();
    }

    /**
     * Disable Vite for testing.
     */
    protected function disableViteForTests(): void
    {
        // Bind a fake Vite instance
        $this->app->instance(Vite::class, new class
        {
            public function __invoke()
            {
                return '';
            }

            public function __call($name, $arguments)
            {
                return '';
            }

            public function __toString()
            {
                return '';
            }
        });

        // Replace the vite helper function during tests
        if (! function_exists('vite')) {
            function vite(...$args)
            {
                return '';
            }
        }
    }
}
