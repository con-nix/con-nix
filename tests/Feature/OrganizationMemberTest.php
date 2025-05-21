<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationMemberTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an organization owner can view the members list.
     */
    public function test_organization_owner_can_view_members_list(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);
        $member = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)
            ->get(route('organizations.members.index', $organization));

        $response->assertStatus(200)
            ->assertViewIs('organizations.members.index')
            ->assertViewHas('organization', $organization)
            ->assertViewHas('members')
            ->assertSee($member->user->name);
    }

    /**
     * Test that a member can view the members list.
     */
    public function test_organization_member_can_view_members_list(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);
        
        $memberUser = User::factory()->create();
        $member = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $memberUser->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($memberUser)
            ->get(route('organizations.members.index', $organization));

        $response->assertStatus(200)
            ->assertViewIs('organizations.members.index')
            ->assertViewHas('organization', $organization)
            ->assertViewHas('members');
    }

    /**
     * Test that a non-member cannot view the members list.
     */
    public function test_non_member_cannot_view_members_list(): void
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($nonMember)
            ->get(route('organizations.members.index', $organization));

        $response->assertStatus(403);
    }

    /**
     * Test that an organization owner can update a member's role.
     */
    public function test_organization_owner_can_update_member_role(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);
        
        $memberUser = User::factory()->create();
        $member = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $memberUser->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($owner)
            ->patch(route('organizations.members.update', [$organization, $member]), [
                'role' => 'admin',
            ]);

        $response->assertStatus(302)
            ->assertRedirect();

        $this->assertDatabaseHas('organization_members', [
            'id' => $member->id,
            'role' => 'admin',
        ]);
    }

    /**
     * Test that an organization admin can update a member's role.
     */
    public function test_organization_admin_can_update_member_role(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);
        
        $adminUser = User::factory()->create();
        $admin = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $adminUser->id,
            'role' => 'admin',
        ]);
        
        $memberUser = User::factory()->create();
        $member = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $memberUser->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($adminUser)
            ->patch(route('organizations.members.update', [$organization, $member]), [
                'role' => 'viewer',
            ]);

        $response->assertStatus(302)
            ->assertRedirect();

        $this->assertDatabaseHas('organization_members', [
            'id' => $member->id,
            'role' => 'viewer',
        ]);
    }

    /**
     * Test that a regular member cannot update another member's role.
     */
    public function test_regular_member_cannot_update_member_role(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);
        
        $memberUser1 = User::factory()->create();
        $member1 = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $memberUser1->id,
            'role' => 'member',
        ]);
        
        $memberUser2 = User::factory()->create();
        $member2 = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $memberUser2->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($memberUser1)
            ->patch(route('organizations.members.update', [$organization, $member2]), [
                'role' => 'viewer',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('organization_members', [
            'id' => $member2->id,
            'role' => 'member', // Role should not change
        ]);
    }

    /**
     * Test that an organization owner can remove a member.
     */
    public function test_organization_owner_can_remove_member(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);
        
        $memberUser = User::factory()->create();
        $member = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $memberUser->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($owner)
            ->delete(route('organizations.members.destroy', [$organization, $member]));

        $response->assertStatus(302)
            ->assertRedirect();

        $this->assertDatabaseMissing('organization_members', [
            'id' => $member->id,
        ]);
    }

    /**
     * Test that a member can remove themselves.
     */
    public function test_member_can_remove_themselves(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);
        
        $memberUser = User::factory()->create();
        $member = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $memberUser->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($memberUser)
            ->delete(route('organizations.members.destroy', [$organization, $member]));

        $response->assertStatus(302)
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'You have left the organization.');

        $this->assertDatabaseMissing('organization_members', [
            'id' => $member->id,
        ]);
    }

    /**
     * Test that the organization owner cannot be removed.
     */
    public function test_organization_owner_cannot_be_removed(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);
        
        // Add the owner as a member too (would be created automatically in practice)
        $ownerMember = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'role' => 'admin',
        ]);

        $adminUser = User::factory()->create();
        $admin = OrganizationMember::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $adminUser->id,
            'role' => 'admin',
        ]);

        // Admin tries to remove the owner
        $response = $this->actingAs($adminUser)
            ->delete(route('organizations.members.destroy', [$organization, $ownerMember]));

        $response->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', 'Cannot remove the organization owner from the organization.');

        $this->assertDatabaseHas('organization_members', [
            'id' => $ownerMember->id,
        ]);
    }
}
