<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrganizationInviteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an organization owner can access the invite form.
     */
    public function test_organization_owner_can_access_invite_form(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('organizations.invites.create', $organization));

        $response->assertStatus(200)
            ->assertViewIs('organizations.invites.create')
            ->assertViewHas('organization', $organization);
    }

    /**
     * Test that a non-owner cannot access the invite form.
     */
    public function test_non_owner_cannot_access_invite_form(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($nonOwner)
            ->get(route('organizations.invites.create', $organization));

        $response->assertStatus(403);
    }

    /**
     * Test that an organization owner can create an invite.
     */
    public function test_organization_owner_can_create_invite(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);
        
        $inviteData = [
            'email' => 'newmember@example.com',
            'role' => 'member',
        ];

        $response = $this->actingAs($user)
            ->post(route('organizations.invites.store', $organization), $inviteData);

        $response->assertStatus(302)
            ->assertRedirect(route('organizations.show', $organization))
            ->assertSessionHas('status', 'Invitation sent successfully.');

        $this->assertDatabaseHas('organization_invites', [
            'organization_id' => $organization->id,
            'sender_id' => $user->id,
            'email' => 'newmember@example.com',
            'role' => 'member',
        ]);
    }

    /**
     * Test that an invite can be viewed with a valid token.
     */
    public function test_invite_can_be_viewed_with_valid_token(): void
    {
        $organization = Organization::factory()->create();
        $invite = OrganizationInvite::factory()->create([
            'organization_id' => $organization->id,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $response = $this->get(route('invites.show', $invite->token));

        $response->assertStatus(200)
            ->assertViewIs('organizations.invites.show')
            ->assertViewHas('invite', $invite)
            ->assertViewHas('organization', $organization);
    }

    /**
     * Test that an expired invite cannot be viewed.
     */
    public function test_expired_invite_cannot_be_viewed(): void
    {
        $organization = Organization::factory()->create();
        $invite = OrganizationInvite::factory()->create([
            'organization_id' => $organization->id,
            'expires_at' => Carbon::now()->subDays(1), // Expired
        ]);

        $response = $this->get(route('invites.show', $invite->token));

        $response->assertStatus(404);
    }

    /**
     * Test that a user with matching email can accept an invite.
     */
    public function test_user_with_matching_email_can_accept_invite(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        
        $invite = OrganizationInvite::factory()->create([
            'organization_id' => $organization->id,
            'email' => $user->email,
            'role' => 'member',
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
            ->post(route('invites.accept', $invite->token));

        $response->assertStatus(302)
            ->assertRedirect(route('organizations.show', $organization))
            ->assertSessionHas('status', 'You have joined the organization successfully.');

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        // Check that invite was marked as accepted
        $this->assertDatabaseHas('organization_invites', [
            'id' => $invite->id,
        ]);
        
        $invite->refresh();
        $this->assertNotNull($invite->accepted_at);
    }

    /**
     * Test that a user with non-matching email cannot accept an invite.
     */
    public function test_user_with_non_matching_email_cannot_accept_invite(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['email' => 'user@example.com']);
        
        $invite = OrganizationInvite::factory()->create([
            'organization_id' => $organization->id,
            'email' => 'different@example.com',
            'role' => 'member',
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
            ->post(route('invites.accept', $invite->token));

        $response->assertStatus(403);

        $this->assertDatabaseMissing('organization_members', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that an invite can be cancelled by an organization owner.
     */
    public function test_invite_can_be_cancelled_by_organization_owner(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);
        $invite = OrganizationInvite::factory()->create([
            'organization_id' => $organization->id,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
            ->delete(route('organizations.invites.cancel', [$organization, $invite]));

        $response->assertStatus(302)
            ->assertRedirect(route('organizations.show', $organization))
            ->assertSessionHas('status', 'Invitation cancelled successfully.');

        $this->assertDatabaseMissing('organization_invites', [
            'id' => $invite->id,
        ]);
    }
}
