<?php

use App\Http\Controllers\ExploreController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RepositoryController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('explore', [ExploreController::class, 'index'])->middleware('auth')->name('explore');

Route::middleware(['auth'])->group(function () {
    // Settings routes
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Repository routes
    Route::resource('repositories', RepositoryController::class);
    Route::get('repositories/{repository}/transfer', [RepositoryController::class, 'transferForm'])->name('repositories.transfer.form');
    Route::patch('repositories/{repository}/transfer', [RepositoryController::class, 'transfer'])->name('repositories.transfer');

    // Organization routes
    Route::resource('organizations', OrganizationController::class);
});

require __DIR__.'/auth.php';
