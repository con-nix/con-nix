<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Follow a user.
     */
    public function store(User $user)
    {
        if (auth()->id() === $user->id) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'You cannot follow yourself.'], 422);
            }
            return back()->with('error', 'You cannot follow yourself.');
        }

        auth()->user()->follow($user);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "You are now following {$user->name}.",
                'following' => true,
            ]);
        }

        return back()->with('status', "You are now following {$user->name}.");
    }

    /**
     * Unfollow a user.
     */
    public function destroy(User $user)
    {
        auth()->user()->unfollow($user);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "You are no longer following {$user->name}.",
                'following' => false,
            ]);
        }

        return back()->with('status', "You are no longer following {$user->name}.");
    }

    /**
     * Get a user's followers.
     */
    public function followers(User $user)
    {
        $followers = $user->followers()
            ->select(['id', 'name', 'email', 'created_at'])
            ->paginate(20);

        return view('users.followers', compact('user', 'followers'));
    }

    /**
     * Get users that a user is following.
     */
    public function following(User $user)
    {
        $following = $user->following()
            ->select(['id', 'name', 'email', 'created_at'])
            ->paginate(20);

        return view('users.following', compact('user', 'following'));
    }

    /**
     * Get activity feed for the authenticated user.
     */
    public function feed()
    {
        $activities = auth()->user()->getActivityFeed();
        
        return view('feed.index', compact('activities'));
    }
}
