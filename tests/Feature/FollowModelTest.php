<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class FollowModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test follow relationship creation.
     */
    public function test_follow_relationship_creation(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        
        $follow = Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
        
        $this->assertEquals($follower->id, $follow->follower_id);
        $this->assertEquals($following->id, $follow->following_id);
        $this->assertNotNull($follow->created_at);
        $this->assertNotNull($follow->updated_at);
    }

    /**
     * Test follow belongs to follower relationship.
     */
    public function test_follow_belongs_to_follower(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        
        $follow = Follow::factory()->create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
        
        $this->assertInstanceOf(User::class, $follow->follower);
        $this->assertEquals($follower->id, $follow->follower->id);
        $this->assertEquals($follower->name, $follow->follower->name);
    }

    /**
     * Test follow belongs to following relationship.
     */
    public function test_follow_belongs_to_following(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        
        $follow = Follow::factory()->create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
        
        $this->assertInstanceOf(User::class, $follow->following);
        $this->assertEquals($following->id, $follow->following->id);
        $this->assertEquals($following->name, $follow->following->name);
    }

    /**
     * Test unique constraint on follower_id and following_id.
     */
    public function test_unique_constraint_on_follow_relationship(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        
        // Create first follow relationship
        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
        
        // Attempt to create duplicate relationship should fail
        $this->expectException(QueryException::class);
        
        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
    }

    /**
     * Test user can follow multiple users.
     */
    public function test_user_can_follow_multiple_users(): void
    {
        $follower = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        Follow::create(['follower_id' => $follower->id, 'following_id' => $user1->id]);
        Follow::create(['follower_id' => $follower->id, 'following_id' => $user2->id]);
        Follow::create(['follower_id' => $follower->id, 'following_id' => $user3->id]);
        
        $followedUsers = Follow::where('follower_id', $follower->id)->get();
        
        $this->assertEquals(3, $followedUsers->count());
        $this->assertTrue($followedUsers->contains('following_id', $user1->id));
        $this->assertTrue($followedUsers->contains('following_id', $user2->id));
        $this->assertTrue($followedUsers->contains('following_id', $user3->id));
    }

    /**
     * Test user can be followed by multiple users.
     */
    public function test_user_can_be_followed_by_multiple_users(): void
    {
        $following = User::factory()->create();
        $follower1 = User::factory()->create();
        $follower2 = User::factory()->create();
        $follower3 = User::factory()->create();
        
        Follow::create(['follower_id' => $follower1->id, 'following_id' => $following->id]);
        Follow::create(['follower_id' => $follower2->id, 'following_id' => $following->id]);
        Follow::create(['follower_id' => $follower3->id, 'following_id' => $following->id]);
        
        $followers = Follow::where('following_id', $following->id)->get();
        
        $this->assertEquals(3, $followers->count());
        $this->assertTrue($followers->contains('follower_id', $follower1->id));
        $this->assertTrue($followers->contains('follower_id', $follower2->id));
        $this->assertTrue($followers->contains('follower_id', $follower3->id));
    }

    /**
     * Test follow factory creates valid relationships.
     */
    public function test_follow_factory_creates_valid_relationships(): void
    {
        $follow = Follow::factory()->create();
        
        $this->assertNotNull($follow->follower_id);
        $this->assertNotNull($follow->following_id);
        $this->assertNotEquals($follow->follower_id, $follow->following_id);
        $this->assertInstanceOf(User::class, $follow->follower);
        $this->assertInstanceOf(User::class, $follow->following);
    }

    /**
     * Test follow factory with specific users.
     */
    public function test_follow_factory_with_specific_users(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        
        $follow = Follow::factory()->create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
        
        $this->assertEquals($follower->id, $follow->follower_id);
        $this->assertEquals($following->id, $follow->following_id);
        $this->assertEquals($follower->name, $follow->follower->name);
        $this->assertEquals($following->name, $follow->following->name);
    }

    /**
     * Test follow fillable attributes.
     */
    public function test_follow_fillable_attributes(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        
        $follow = new Follow();
        $follow->fill([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'id' => 999, // Should not be fillable
            'created_at' => now()->subYear(), // Should not be fillable
        ]);
        
        $this->assertEquals($follower->id, $follow->follower_id);
        $this->assertEquals($following->id, $follow->following_id);
        
        // These should not be set via fill
        $this->assertNull($follow->id);
        $this->assertNull($follow->created_at);
    }

    /**
     * Test deleting follow relationship.
     */
    public function test_deleting_follow_relationship(): void
    {
        $follow = Follow::factory()->create();
        $followId = $follow->id;
        
        $this->assertEquals(1, Follow::count());
        
        $follow->delete();
        
        $this->assertEquals(0, Follow::count());
        $this->assertNull(Follow::find($followId));
    }

    /**
     * Test cascade deletion when user is deleted.
     */
    public function test_cascade_deletion_when_follower_deleted(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        
        $follow = Follow::factory()->create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
        
        $this->assertEquals(1, Follow::count());
        
        $follower->delete();
        
        $this->assertEquals(0, Follow::count());
    }

    /**
     * Test cascade deletion when followed user is deleted.
     */
    public function test_cascade_deletion_when_following_deleted(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();
        
        $follow = Follow::factory()->create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
        
        $this->assertEquals(1, Follow::count());
        
        $following->delete();
        
        $this->assertEquals(0, Follow::count());
    }

    /**
     * Test follow relationship with same user IDs but different roles.
     */
    public function test_mutual_follow_relationships(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // User1 follows User2
        $follow1 = Follow::create([
            'follower_id' => $user1->id,
            'following_id' => $user2->id,
        ]);
        
        // User2 follows User1
        $follow2 = Follow::create([
            'follower_id' => $user2->id,
            'following_id' => $user1->id,
        ]);
        
        $this->assertEquals(2, Follow::count());
        $this->assertNotEquals($follow1->id, $follow2->id);
        
        // Verify relationships
        $this->assertEquals($user1->id, $follow1->follower_id);
        $this->assertEquals($user2->id, $follow1->following_id);
        $this->assertEquals($user2->id, $follow2->follower_id);
        $this->assertEquals($user1->id, $follow2->following_id);
    }

    /**
     * Test follow timestamps are automatically managed.
     */
    public function test_follow_timestamps_automatically_managed(): void
    {
        $follow = Follow::factory()->create();
        
        $this->assertNotNull($follow->created_at);
        $this->assertNotNull($follow->updated_at);
        $this->assertEquals($follow->created_at->toDateTimeString(), $follow->updated_at->toDateTimeString());
        
        // Update the follow relationship
        $originalUpdatedAt = $follow->updated_at;
        sleep(1); // Ensure time difference
        $follow->touch();
        
        $this->assertNotEquals($originalUpdatedAt->toDateTimeString(), $follow->fresh()->updated_at->toDateTimeString());
        $this->assertEquals($follow->created_at->toDateTimeString(), $follow->fresh()->created_at->toDateTimeString());
    }

    /**
     * Test querying follows by follower.
     */
    public function test_querying_follows_by_follower(): void
    {
        $follower = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $otherUser = User::factory()->create();
        
        Follow::factory()->create(['follower_id' => $follower->id, 'following_id' => $user1->id]);
        Follow::factory()->create(['follower_id' => $follower->id, 'following_id' => $user2->id]);
        Follow::factory()->create(['follower_id' => $otherUser->id, 'following_id' => $user1->id]);
        
        $followerFollows = Follow::where('follower_id', $follower->id)->get();
        
        $this->assertEquals(2, $followerFollows->count());
        $this->assertTrue($followerFollows->contains('following_id', $user1->id));
        $this->assertTrue($followerFollows->contains('following_id', $user2->id));
        $this->assertFalse($followerFollows->contains('following_id', $otherUser->id));
    }

    /**
     * Test querying follows by following.
     */
    public function test_querying_follows_by_following(): void
    {
        $following = User::factory()->create();
        $follower1 = User::factory()->create();
        $follower2 = User::factory()->create();
        $otherUser = User::factory()->create();
        
        Follow::factory()->create(['follower_id' => $follower1->id, 'following_id' => $following->id]);
        Follow::factory()->create(['follower_id' => $follower2->id, 'following_id' => $following->id]);
        Follow::factory()->create(['follower_id' => $follower1->id, 'following_id' => $otherUser->id]);
        
        $followingFollows = Follow::where('following_id', $following->id)->get();
        
        $this->assertEquals(2, $followingFollows->count());
        $this->assertTrue($followingFollows->contains('follower_id', $follower1->id));
        $this->assertTrue($followingFollows->contains('follower_id', $follower2->id));
        $this->assertFalse($followingFollows->contains('follower_id', $otherUser->id));
    }
}
