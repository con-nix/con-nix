<?php

require_once __DIR__.'/../vendor/autoload.php';

// Make sure the function exists in our test environment
if (! function_exists('vite')) {
    function vite($args = null)
    {
        return '';
    }
}

// Create a stub for fluxAppearance if it doesn't exist
if (! function_exists('fluxAppearance')) {
    function fluxAppearance()
    {
        return '';
    }
}
