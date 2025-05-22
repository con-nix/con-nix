<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can follow another user.
     */
    public function test_user_can_follow_another_user(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $response = $this->actingAs($follower)
            ->post(route('users.follow', $following));

        $response->assertStatus(302);

        $this->assertTrue($follower->isFollowing($following));
        $this->assertTrue($following->isFollowedBy($follower));

        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
    }

    /**
     * Test that a user can unfollow another user.
     */
    public function test_user_can_unfollow_another_user(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        // First follow the user
        $follower->follow($following);
        $this->assertTrue($follower->isFollowing($following));

        // Then unfollow
        $response = $this->actingAs($follower)
            ->delete(route('users.unfollow', $following));

        $response->assertStatus(302);

        $this->assertFalse($follower->isFollowing($following));
        $this->assertFalse($following->isFollowedBy($follower));

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
    }

    /**
     * Test that a user cannot follow themselves.
     */
    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('users.follow', $user));

        $response->assertStatus(302)
            ->assertSessionHas('error', 'You cannot follow yourself.');

        $this->assertFalse($user->isFollowing($user));
    }

    /**
     * Test that following a user creates a notification.
     */
    public function test_following_user_creates_notification(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $this->assertEquals(0, $following->notifications()->count());

        $follower->follow($following);

        $this->assertEquals(1, $following->notifications()->count());
        $notification = $following->notifications()->first();
        
        $this->assertEquals('user_follow', $notification->type);
        $this->assertEquals("{$follower->name} started following you", $notification->title);
    }

    /**
     * Test that users can view followers list.
     */
    public function test_user_can_view_followers_list(): void
    {
        $user = User::factory()->create();
        $followers = User::factory()->count(3)->create();

        foreach ($followers as $follower) {
            $follower->follow($user);
        }

        $response = $this->actingAs($user)
            ->get(route('users.followers', $user));

        $response->assertStatus(200)
            ->assertViewIs('users.followers')
            ->assertViewHas('user', $user)
            ->assertViewHas('followers');

        foreach ($followers as $follower) {
            $response->assertSee($follower->name);
        }
    }

    /**
     * Test that users can view following list.
     */
    public function test_user_can_view_following_list(): void
    {
        $user = User::factory()->create();
        $following = User::factory()->count(3)->create();

        foreach ($following as $followedUser) {
            $user->follow($followedUser);
        }

        $response = $this->actingAs($user)
            ->get(route('users.following', $user));

        $response->assertStatus(200)
            ->assertViewIs('users.following')
            ->assertViewHas('user', $user)
            ->assertViewHas('following');

        foreach ($following as $followedUser) {
            $response->assertSee($followedUser->name);
        }
    }

    /**
     * Test that activity feed shows activities from followed users.
     */
    public function test_activity_feed_shows_followed_users_activities(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();
        $notFollowedUser = User::factory()->create();

        // User follows followedUser
        $user->follow($followedUser);

        // Create activities for both users
        $followedUserActivity = $followedUser->recordActivity('test', 'Followed user activity');
        $notFollowedUserActivity = $notFollowedUser->recordActivity('test', 'Not followed user activity');

        $feed = $user->getActivityFeed();

        // Should include followed user's activity and own activity
        $this->assertTrue($feed->contains('id', $followedUserActivity->id));
        
        // Should not include non-followed user's activity
        $this->assertFalse($feed->contains('id', $notFollowedUserActivity->id));
    }

    /**
     * Test follower and following counts.
     */
    public function test_follower_and_following_counts(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // user1 follows user2 and user3
        $user1->follow($user2);
        $user1->follow($user3);

        // user3 follows user1
        $user3->follow($user1);

        $this->assertEquals(2, $user1->following_count); // user1 follows 2 users
        $this->assertEquals(1, $user1->followers_count); // user1 has 1 follower (user3)

        $this->assertEquals(0, $user2->following_count); // user2 follows 0 users
        $this->assertEquals(1, $user2->followers_count); // user2 has 1 follower (user1)

        $this->assertEquals(1, $user3->following_count); // user3 follows 1 user
        $this->assertEquals(1, $user3->followers_count); // user3 has 1 follower (user1)
    }

    /**
     * Test that duplicate follows are prevented.
     */
    public function test_duplicate_follows_are_prevented(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        // Follow once
        $follower->follow($following);
        $this->assertEquals(1, Follow::count());

        // Try to follow again
        $follower->follow($following);
        $this->assertEquals(1, Follow::count()); // Should still be 1
    }

    /**
     * Test activity feed includes own activities.
     */
    public function test_activity_feed_includes_own_activities(): void
    {
        $user = User::factory()->create();
        
        $activity = $user->recordActivity('test', 'Own activity');
        
        $feed = $user->getActivityFeed();
        
        $this->assertTrue($feed->contains('id', $activity->id));
    }
}
