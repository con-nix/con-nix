<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that unauthenticated users cannot access organization pages.
     */
    public function test_unauthenticated_users_cannot_access_organization_pages(): void
    {
        $this->get(route('organizations.index'))->assertRedirect(route('login'));
        $this->get(route('organizations.create'))->assertRedirect(route('login'));
        $this->post(route('organizations.store'), [])->assertRedirect(route('login'));

        $organization = Organization::factory()->create();

        $this->get(route('organizations.show', $organization))->assertRedirect(route('login'));
        $this->get(route('organizations.edit', $organization))->assertRedirect(route('login'));
        $this->put(route('organizations.update', $organization), [])->assertRedirect(route('login'));
        $this->delete(route('organizations.destroy', $organization))->assertRedirect(route('login'));
    }

    /**
     * Test that users can view the organizations index page.
     */
    public function test_users_can_view_organizations_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->get(route('organizations.index'));

        $response->assertSuccessful();
    }

    /**
     * Test that users can create organizations.
     */
    public function test_users_can_create_organizations(): void
    {
        $user = User::factory()->create();

        $organizationData = [
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('organizations.store'), $organizationData);

        $this->assertDatabaseHas('organizations', [
            'name' => $organizationData['name'],
            'description' => $organizationData['description'],
            'owner_id' => $user->id,
        ]);

        $response->assertSuccessful();
    }

    /**
     * Test that users can view their organizations.
     */
    public function test_users_can_view_their_organizations(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->get(route('organizations.show', $organization));

        $response->assertSuccessful();
    }

    /**
     * Test that users cannot view organizations they don't own.
     */
    public function test_users_cannot_view_others_organizations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('organizations.show', $organization));

        $response->assertStatus(403);
    }

    /**
     * Test that users can edit their organizations.
     */
    public function test_users_can_edit_their_organizations(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        $updatedData = [
            'name' => 'Updated Organization Name',
            'description' => 'Updated organization description',
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->put(route('organizations.update', $organization), $updatedData);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => $updatedData['name'],
            'description' => $updatedData['description'],
        ]);

        $response->assertSuccessful();
    }

    /**
     * Test that users cannot edit organizations they don't own.
     */
    public function test_users_cannot_edit_others_organizations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('organizations.edit', $organization));
        $response->assertStatus(403);

        $updatedData = [
            'name' => 'Updated Organization Name',
            'description' => 'Updated organization description',
        ];

        $response = $this->actingAs($user)->put(route('organizations.update', $organization), $updatedData);
        $response->assertStatus(403);
    }

    /**
     * Test that users can delete their organizations.
     */
    public function test_users_can_delete_their_organizations(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->delete(route('organizations.destroy', $organization));

        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
        $response->assertSuccessful();
    }

    /**
     * Test that users cannot delete organizations they don't own.
     */
    public function test_users_cannot_delete_others_organizations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('organizations.destroy', $organization));

        $response->assertStatus(403);
        $this->assertDatabaseHas('organizations', ['id' => $organization->id]);
    }
}
