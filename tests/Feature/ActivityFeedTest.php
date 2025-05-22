<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Follow;
use App\Models\Organization;
use App\Models\Repository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityFeedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test activity feed includes own activities.
     */
    public function test_activity_feed_includes_own_activities(): void
    {
        $user = User::factory()->create();
        
        // Create some activities for the user
        $activity1 = $user->recordActivity('test_action_1', 'First activity');
        $activity2 = $user->recordActivity('test_action_2', 'Second activity');
        
        $feed = $user->getActivityFeed();
        
        $this->assertEquals(2, $feed->count());
        $this->assertTrue($feed->contains('id', $activity1->id));
        $this->assertTrue($feed->contains('id', $activity2->id));
    }

    /**
     * Test activity feed includes followed users' activities.
     */
    public function test_activity_feed_includes_followed_users_activities(): void
    {
        $user = User::factory()->create();
        $followedUser1 = User::factory()->create();
        $followedUser2 = User::factory()->create();
        $notFollowedUser = User::factory()->create();
        
        // User follows two other users
        $user->follow($followedUser1);
        $user->follow($followedUser2);
        
        // Create activities for all users
        $userActivity = $user->recordActivity('user_action', 'User activity');
        $followed1Activity = $followedUser1->recordActivity('followed1_action', 'Followed1 activity');
        $followed2Activity = $followedUser2->recordActivity('followed2_action', 'Followed2 activity');
        $notFollowedActivity = $notFollowedUser->recordActivity('not_followed_action', 'Not followed activity');
        
        $feed = $user->getActivityFeed();
        
        $this->assertEquals(3, $feed->count());
        $this->assertTrue($feed->contains('id', $userActivity->id));
        $this->assertTrue($feed->contains('id', $followed1Activity->id));
        $this->assertTrue($feed->contains('id', $followed2Activity->id));
        $this->assertFalse($feed->contains('id', $notFollowedActivity->id));
    }

    /**
     * Test activity feed excludes not followed users' activities.
     */
    public function test_activity_feed_excludes_not_followed_users_activities(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();
        $notFollowedUser1 = User::factory()->create();
        $notFollowedUser2 = User::factory()->create();
        
        // User only follows one user
        $user->follow($followedUser);
        
        // Create activities for all users
        $followedActivity = $followedUser->recordActivity('followed_action', 'Followed activity');
        $notFollowed1Activity = $notFollowedUser1->recordActivity('not_followed1_action', 'Not followed1 activity');
        $notFollowed2Activity = $notFollowedUser2->recordActivity('not_followed2_action', 'Not followed2 activity');
        
        $feed = $user->getActivityFeed();
        
        $this->assertEquals(1, $feed->count());
        $this->assertTrue($feed->contains('id', $followedActivity->id));
        $this->assertFalse($feed->contains('id', $notFollowed1Activity->id));
        $this->assertFalse($feed->contains('id', $notFollowed2Activity->id));
    }

    /**
     * Test activity feed is ordered by most recent first.
     */
    public function test_activity_feed_is_ordered_by_most_recent_first(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();
        
        $user->follow($followedUser);
        
        // Create activities with different timestamps
        $oldActivity = Activity::factory()->create([
            'user_id' => $followedUser->id,
            'created_at' => Carbon::now()->subDays(5),
            'description' => 'Old activity',
        ]);
        
        $mediumActivity = Activity::factory()->create([
            'user_id' => $user->id,
            'created_at' => Carbon::now()->subDays(2),
            'description' => 'Medium activity',
        ]);
        
        $recentActivity = Activity::factory()->create([
            'user_id' => $followedUser->id,
            'created_at' => Carbon::now(),
            'description' => 'Recent activity',
        ]);
        
        $feed = $user->getActivityFeed();
        
        $this->assertEquals(3, $feed->count());
        $this->assertEquals($recentActivity->id, $feed->first()->id);
        $this->assertEquals($mediumActivity->id, $feed->get(1)->id);
        $this->assertEquals($oldActivity->id, $feed->last()->id);
    }

    /**
     * Test activity feed respects limit parameter.
     */
    public function test_activity_feed_respects_limit_parameter(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();
        
        $user->follow($followedUser);
        
        // Create 10 activities
        for ($i = 0; $i < 10; $i++) {
            $followedUser->recordActivity("action_$i", "Activity $i");
        }
        
        // Test default limit (50)
        $fullFeed = $user->getActivityFeed();
        $this->assertEquals(10, $fullFeed->count());
        
        // Test custom limit
        $limitedFeed = $user->getActivityFeed(5);
        $this->assertEquals(5, $limitedFeed->count());
        
        // Test limit of 1
        $singleFeed = $user->getActivityFeed(1);
        $this->assertEquals(1, $singleFeed->count());
    }

    /**
     * Test activity feed with no followed users.
     */
    public function test_activity_feed_with_no_followed_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // User doesn't follow anyone
        $userActivity = $user->recordActivity('user_action', 'User activity');
        $otherActivity = $otherUser->recordActivity('other_action', 'Other activity');
        
        $feed = $user->getActivityFeed();
        
        $this->assertEquals(1, $feed->count());
        $this->assertTrue($feed->contains('id', $userActivity->id));
        $this->assertFalse($feed->contains('id', $otherActivity->id));
    }

    /**
     * Test activity feed with no activities.
     */
    public function test_activity_feed_with_no_activities(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();
        
        $user->follow($followedUser);
        
        $feed = $user->getActivityFeed();
        
        $this->assertEquals(0, $feed->count());
        $this->assertTrue($feed->isEmpty());
    }

    /**
     * Test activity feed includes relationships with models.
     */
    public function test_activity_feed_includes_relationships_with_models(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();
        $repository = Repository::factory()->create();
        
        $user->follow($followedUser);
        
        $activity = $followedUser->recordActivity(
            'repository_action',
            'Repository activity',
            $repository
        );
        
        $feed = $user->getActivityFeed();
        
        $this->assertEquals(1, $feed->count());
        $feedActivity = $feed->first();
        
        $this->assertEquals($activity->id, $feedActivity->id);
        $this->assertInstanceOf(User::class, $feedActivity->user);
        $this->assertEquals($followedUser->id, $feedActivity->user->id);
        $this->assertInstanceOf(Repository::class, $feedActivity->subject);
        $this->assertEquals($repository->id, $feedActivity->subject->id);
    }

    /**
     * Test activity feed updates when following changes.
     */
    public function test_activity_feed_updates_when_following_changes(): void
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $activity1 = $user1->recordActivity('action1', 'Activity 1');
        $activity2 = $user2->recordActivity('action2', 'Activity 2');
        
        // Initially, feed should be empty (no follows, no own activities)
        $feed = $user->getActivityFeed();
        $this->assertEquals(0, $feed->count());
        
        // Follow user1, should see their activity
        $user->follow($user1);
        $feed = $user->getActivityFeed();
        $this->assertEquals(1, $feed->count());
        $this->assertTrue($feed->contains('id', $activity1->id));
        
        // Follow user2, should see both activities
        $user->follow($user2);
        $feed = $user->getActivityFeed();
        $this->assertEquals(2, $feed->count());
        $this->assertTrue($feed->contains('id', $activity1->id));
        $this->assertTrue($feed->contains('id', $activity2->id));
        
        // Unfollow user1, should only see user2's activity
        $user->unfollow($user1);
        $feed = $user->getActivityFeed();
        $this->assertEquals(1, $feed->count());
        $this->assertFalse($feed->contains('id', $activity1->id));
        $this->assertTrue($feed->contains('id', $activity2->id));
    }

    /**
     * Test activity feed view endpoint.
     */
    public function test_activity_feed_view_endpoint(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();
        
        $user->follow($followedUser);
        
        $activity = $followedUser->recordActivity('test_action', 'Test activity');
        
        $response = $this->actingAs($user)->get(route('feed'));
        
        $response->assertStatus(200)
            ->assertViewIs('feed.index')
            ->assertViewHas('activities')
            ->assertSee('Test activity');
        
        $activities = $response->viewData('activities');
        $this->assertEquals(1, $activities->count());
        $this->assertEquals($activity->id, $activities->first()->id);
    }

    /**
     * Test empty activity feed view.
     */
    public function test_empty_activity_feed_view(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('feed'));
        
        $response->assertStatus(200)
            ->assertViewIs('feed.index')
            ->assertViewHas('activities')
            ->assertSee('No activity yet');
        
        $activities = $response->viewData('activities');
        $this->assertEquals(0, $activities->count());
    }

    /**
     * Test activity feed includes complex scenario.
     */
    public function test_activity_feed_complex_scenario(): void
    {
        // Create multiple users
        $user = User::factory()->create();
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);
        $charlie = User::factory()->create(['name' => 'Charlie']);
        
        // User follows Alice and Bob, but not Charlie
        $user->follow($alice);
        $user->follow($bob);
        
        // Create various activities at different times
        $userRepo = Repository::factory()->create(['user_id' => $user->id]);
        $aliceOrg = Organization::factory()->create(['owner_id' => $alice->id]);
        $bobRepo = Repository::factory()->create(['user_id' => $bob->id]);
        $charlieRepo = Repository::factory()->create(['user_id' => $charlie->id]);
        
        // Clear auto-generated activities and create custom ones
        Activity::truncate();
        
        $activities = [
            $user->recordActivity('created_repo', 'Created a repository', $userRepo),
            $alice->recordActivity('created_org', 'Created an organization', $aliceOrg),
            $bob->recordActivity('updated_repo', 'Updated repository', $bobRepo),
            $charlie->recordActivity('created_repo', 'Created repository', $charlieRepo), // Should not appear
        ];
        
        $feed = $user->getActivityFeed();
        
        // Should include user's own activity + Alice's + Bob's (3 total)
        $this->assertEquals(3, $feed->count());
        
        // Verify specific activities are included/excluded
        $this->assertTrue($feed->contains('id', $activities[0]->id)); // User's activity
        $this->assertTrue($feed->contains('id', $activities[1]->id)); // Alice's activity
        $this->assertTrue($feed->contains('id', $activities[2]->id)); // Bob's activity
        $this->assertFalse($feed->contains('id', $activities[3]->id)); // Charlie's activity (not followed)
        
        // Verify activities include proper relationships
        foreach ($feed as $activity) {
            $this->assertInstanceOf(User::class, $activity->user);
            if ($activity->subject) {
                $this->assertTrue(
                    $activity->subject instanceof Repository || 
                    $activity->subject instanceof Organization
                );
            }
        }
    }

    /**
     * Test activity feed performance with large number of activities.
     */
    public function test_activity_feed_performance_with_large_dataset(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();
        
        $user->follow($followedUser);
        
        // Create a large number of activities
        $activities = [];
        for ($i = 0; $i < 100; $i++) {
            $activities[] = Activity::factory()->create([
                'user_id' => $followedUser->id,
                'created_at' => Carbon::now()->subMinutes($i),
            ]);
        }
        
        // Test that feed respects limit and returns correct count
        $limitedFeed = $user->getActivityFeed(20);
        $this->assertEquals(20, $limitedFeed->count());
        
        // Verify most recent activities are returned first
        $firstActivity = $limitedFeed->first();
        $lastActivity = $limitedFeed->last();
        
        $this->assertTrue($firstActivity->created_at->isAfter($lastActivity->created_at));
        
        // Test default limit
        $defaultFeed = $user->getActivityFeed();
        $this->assertEquals(50, $defaultFeed->count()); // Default limit is 50
    }
}
