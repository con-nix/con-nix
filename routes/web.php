<?php

use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationInviteController;
use App\Http\Controllers\OrganizationMemberController;
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
    
    // Organization members routes
    Route::get('organizations/{organization}/members', [OrganizationMemberController::class, 'index'])->name('organizations.members.index');
    Route::patch('organizations/{organization}/members/{member}', [OrganizationMemberController::class, 'update'])->name('organizations.members.update');
    Route::delete('organizations/{organization}/members/{member}', [OrganizationMemberController::class, 'destroy'])->name('organizations.members.destroy');
    
    // Organization invites routes
    Route::get('organizations/{organization}/invites/create', [OrganizationInviteController::class, 'create'])->name('organizations.invites.create');
    Route::post('organizations/{organization}/invites', [OrganizationInviteController::class, 'store'])->name('organizations.invites.store');
    Route::delete('organizations/{organization}/invites/{invite}', [OrganizationInviteController::class, 'cancel'])->name('organizations.invites.cancel');
    
    // Notification routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
    Route::get('api/notifications/recent', [NotificationController::class, 'recent'])->name('api.notifications.recent');
    
    // Follow routes
    Route::post('users/{user}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('users/{user}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');
    Route::get('users/{user}/followers', [FollowController::class, 'followers'])->name('users.followers');
    Route::get('users/{user}/following', [FollowController::class, 'following'])->name('users.following');
    
    // Activity feed route
    Route::get('feed', [FollowController::class, 'feed'])->name('feed');
    
    // Public invite routes (don't require auth middleware)
    Route::get('invites/{token}', [OrganizationInviteController::class, 'show'])->name('invites.show')->withoutMiddleware('auth');
    Route::post('invites/{token}/accept', [OrganizationInviteController::class, 'accept'])->name('invites.accept')->withoutMiddleware('auth');
    Route::post('invites/{token}/decline', [OrganizationInviteController::class, 'decline'])->name('invites.decline')->withoutMiddleware('auth');
});

require __DIR__.'/auth.php';
