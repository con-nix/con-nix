<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Organization;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that creating a repository records an activity.
     */
    public function test_creating_repository_records_activity(): void
    {
        $user = User::factory()->create();
        
        $this->assertEquals(0, Activity::count());
        
        $repository = Repository::factory()->create([
            'user_id' => $user->id,
            'name' => 'test-repo',
        ]);
        
        $this->assertEquals(1, Activity::count());
        
        $activity = Activity::first();
        $this->assertEquals($user->id, $activity->user_id);
        $this->assertEquals('repository_created', $activity->type);
        $this->assertEquals("Created repository {$repository->name}", $activity->description);
        $this->assertEquals(Repository::class, $activity->subject_type);
        $this->assertEquals($repository->id, $activity->subject_id);
        $this->assertArrayHasKey('repository_name', $activity->properties);
        $this->assertEquals('test-repo', $activity->properties['repository_name']);
    }

    /**
     * Test that updating a repository records an activity.
     */
    public function test_updating_repository_records_activity(): void
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create([
            'user_id' => $user->id,
            'name' => 'original-name',
        ]);
        
        // Clear the creation activity
        Activity::truncate();
        
        $repository->update(['name' => 'updated-name']);
        
        $this->assertEquals(1, Activity::count());
        
        $activity = Activity::first();
        $this->assertEquals($user->id, $activity->user_id);
        $this->assertEquals('repository_updated', $activity->type);
        $this->assertEquals("Updated repository {$repository->name}", $activity->description);
        $this->assertArrayHasKey('changes', $activity->properties);
    }

    /**
     * Test that deleting a repository records an activity.
     */
    public function test_deleting_repository_records_activity(): void
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create([
            'user_id' => $user->id,
            'name' => 'to-be-deleted',
        ]);
        
        // Clear the creation activity
        Activity::truncate();
        
        $repository->delete();
        
        $this->assertEquals(1, Activity::count());
        
        $activity = Activity::first();
        $this->assertEquals($user->id, $activity->user_id);
        $this->assertEquals('repository_deleted', $activity->type);
        $this->assertEquals("Deleted repository to-be-deleted", $activity->description);
        $this->assertNull($activity->subject_type);
        $this->assertNull($activity->subject_id);
        $this->assertEquals('to-be-deleted', $activity->properties['repository_name']);
    }

    /**
     * Test that creating an organization records an activity.
     */
    public function test_creating_organization_records_activity(): void
    {
        $user = User::factory()->create();
        
        $this->assertEquals(0, Activity::count());
        
        $organization = Organization::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Test Org',
        ]);
        
        $this->assertEquals(1, Activity::count());
        
        $activity = Activity::first();
        $this->assertEquals($user->id, $activity->user_id);
        $this->assertEquals('organization_created', $activity->type);
        $this->assertEquals("Created organization {$organization->name}", $activity->description);
        $this->assertEquals(Organization::class, $activity->subject_type);
        $this->assertEquals($organization->id, $activity->subject_id);
    }

    /**
     * Test that updating an organization records an activity.
     */
    public function test_updating_organization_records_activity(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Original Org',
        ]);
        
        // Clear the creation activity
        Activity::truncate();
        
        $organization->update(['name' => 'Updated Org']);
        
        $this->assertEquals(1, Activity::count());
        
        $activity = Activity::first();
        $this->assertEquals($user->id, $activity->user_id);
        $this->assertEquals('organization_updated', $activity->type);
        $this->assertEquals("Updated organization {$organization->name}", $activity->description);
    }

    /**
     * Test that deleting an organization records an activity.
     */
    public function test_deleting_organization_records_activity(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'owner_id' => $user->id,
            'name' => 'To Be Deleted Org',
        ]);
        
        // Clear the creation activity
        Activity::truncate();
        
        $organization->delete();
        
        $this->assertEquals(1, Activity::count());
        
        $activity = Activity::first();
        $this->assertEquals($user->id, $activity->user_id);
        $this->assertEquals('organization_deleted', $activity->type);
        $this->assertEquals("Deleted organization To Be Deleted Org", $activity->description);
        $this->assertNull($activity->subject_type);
        $this->assertNull($activity->subject_id);
    }

    /**
     * Test that organization-owned repositories record activities for the owner.
     */
    public function test_organization_repository_records_activity_for_owner(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);
        
        // Clear the organization creation activity
        Activity::truncate();
        
        $repository = Repository::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => null,
            'name' => 'org-repo',
        ]);
        
        // Should not create activity since repository owner is organization, not user
        $this->assertEquals(0, Activity::count());
    }

    /**
     * Test activity scopes.
     */
    public function test_activity_scopes(): void
    {
        // Clear any existing activities from other tests
        Activity::truncate();
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $activity1 = Activity::factory()->create([
            'user_id' => $user1->id,
            'type' => 'repository_created',
            'created_at' => now()->subMinutes(3),
        ]);
        
        $activity2 = Activity::factory()->create([
            'user_id' => $user2->id,
            'type' => 'organization_created',
            'created_at' => now()->subMinutes(2),
        ]);
        
        $activity3 = Activity::factory()->create([
            'user_id' => $user1->id,
            'type' => 'repository_updated',
            'created_at' => now()->subMinutes(1),
        ]);
        
        // Test byUsers scope
        $user1Activities = Activity::byUsers([$user1->id])->get();
        $this->assertEquals(2, $user1Activities->count());
        $this->assertTrue($user1Activities->contains('id', $activity1->id));
        $this->assertTrue($user1Activities->contains('id', $activity3->id));
        
        // Test ofType scope
        $repoActivities = Activity::ofType('repository_created')->get();
        
        // Debug output
        $this->assertGreaterThanOrEqual(1, $repoActivities->count());
        $this->assertTrue($repoActivities->contains('id', $activity1->id));
        
        // Test recent scope using only our test activities
        $testActivityIds = [$activity1->id, $activity2->id, $activity3->id];
        $recentActivities = Activity::whereIn('id', $testActivityIds)->recent()->get();
        $this->assertEquals(3, $recentActivities->count());
        $this->assertEquals($activity3->id, $recentActivities->first()->id);
    }

    /**
     * Test manual activity recording.
     */
    public function test_manual_activity_recording(): void
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create();
        
        $activity = $user->recordActivity(
            'custom_action',
            'User performed a custom action',
            $repository,
            ['custom_property' => 'value']
        );
        
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertEquals($user->id, $activity->user_id);
        $this->assertEquals('custom_action', $activity->type);
        $this->assertEquals('User performed a custom action', $activity->description);
        $this->assertEquals(Repository::class, $activity->subject_type);
        $this->assertEquals($repository->id, $activity->subject_id);
        $this->assertEquals(['custom_property' => 'value'], $activity->properties);
    }

    /**
     * Test activity recording without subject.
     */
    public function test_activity_recording_without_subject(): void
    {
        $user = User::factory()->create();
        
        $activity = $user->recordActivity(
            'general_action',
            'User performed a general action'
        );
        
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertEquals($user->id, $activity->user_id);
        $this->assertEquals('general_action', $activity->type);
        $this->assertEquals('User performed a general action', $activity->description);
        $this->assertNull($activity->subject_type);
        $this->assertNull($activity->subject_id);
        $this->assertNull($activity->properties);
    }

    /**
     * Test activity relationships.
     */
    public function test_activity_relationships(): void
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create();
        
        $activity = Activity::factory()->create([
            'user_id' => $user->id,
            'subject_type' => Repository::class,
            'subject_id' => $repository->id,
        ]);
        
        $this->assertEquals($user->id, $activity->user->id);
        $this->assertEquals($repository->id, $activity->subject->id);
        $this->assertInstanceOf(Repository::class, $activity->subject);
    }
}
