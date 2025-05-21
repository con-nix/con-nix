<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that unauthenticated users cannot access repository pages.
     */
    public function test_unauthenticated_users_cannot_access_repository_pages(): void
    {
        $this->get(route('repositories.index'))->assertRedirect(route('login'));
        $this->get(route('repositories.create'))->assertRedirect(route('login'));
        $this->post(route('repositories.store'), [])->assertRedirect(route('login'));

        $repository = Repository::factory()->create();

        $this->get(route('repositories.show', $repository))->assertRedirect(route('login'));
        $this->get(route('repositories.edit', $repository))->assertRedirect(route('login'));
        $this->put(route('repositories.update', $repository), [])->assertRedirect(route('login'));
        $this->delete(route('repositories.destroy', $repository))->assertRedirect(route('login'));
    }

    /**
     * Test that users can view the repositories index page.
     */
    public function test_users_can_view_repositories_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->get(route('repositories.index'));

        $response->assertSuccessful();
    }

    /**
     * Test that users can create personal repositories.
     */
    public function test_users_can_create_personal_repositories(): void
    {
        $user = User::factory()->create();

        $repositoryData = [
            'name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'is_public' => true,
            'owner_type' => 'user',
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('repositories.store'), $repositoryData);

        $this->assertDatabaseHas('repositories', [
            'name' => $repositoryData['name'],
            'description' => $repositoryData['description'],
            'user_id' => $user->id,
        ]);

        $response->assertSuccessful();
    }

    /**
     * Test that users can create organization repositories.
     */
    public function test_users_can_create_organization_repositories(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        $repositoryData = [
            'name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'is_public' => true,
            'owner_type' => 'organization',
            'organization_id' => $organization->id,
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('repositories.store'), $repositoryData);

        $this->assertDatabaseHas('repositories', [
            'name' => $repositoryData['name'],
            'description' => $repositoryData['description'],
            'organization_id' => $organization->id,
        ]);

        $response->assertSuccessful();
    }

    /**
     * Test that users cannot create repositories in organizations they don't own.
     */
    public function test_users_cannot_create_repositories_in_others_organizations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $otherUser->id]);

        $repositoryData = [
            'name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'is_public' => true,
            'owner_type' => 'organization',
            'organization_id' => $organization->id,
        ];

        $response = $this->actingAs($user)->post(route('repositories.store'), $repositoryData);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('repositories', [
            'name' => $repositoryData['name'],
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Test that users can view their repositories.
     */
    public function test_users_can_view_their_repositories(): void
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->get(route('repositories.show', $repository));

        $response->assertSuccessful();
    }

    /**
     * Test that users can view public repositories.
     */
    public function test_users_can_view_public_repositories(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $repository = Repository::factory()->create([
            'user_id' => $otherUser->id,
            'is_public' => true,
        ]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->get(route('repositories.show', $repository));

        $response->assertSuccessful();
    }

    /**
     * Test that users cannot view private repositories they don't own.
     */
    public function test_users_cannot_view_others_private_repositories(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $repository = Repository::factory()->create([
            'user_id' => $otherUser->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($user)->get(route('repositories.show', $repository));

        $response->assertStatus(403);
    }

    /**
     * Test that users can edit their repositories.
     */
    public function test_users_can_edit_their_repositories(): void
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create(['user_id' => $user->id]);

        $updatedData = [
            'name' => 'Updated Repository Name',
            'description' => 'Updated repository description',
            'is_public' => false,
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->put(route('repositories.update', $repository), $updatedData);

        $this->assertDatabaseHas('repositories', [
            'id' => $repository->id,
            'name' => $updatedData['name'],
            'description' => $updatedData['description'],
            'is_public' => false,
        ]);

        $response->assertSuccessful();
    }

    /**
     * Test that users cannot edit repositories they don't own.
     */
    public function test_users_cannot_edit_others_repositories(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $repository = Repository::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('repositories.edit', $repository));
        $response->assertStatus(403);

        $updatedData = [
            'name' => 'Updated Repository Name',
            'description' => 'Updated repository description',
            'is_public' => false,
        ];

        $response = $this->actingAs($user)->put(route('repositories.update', $repository), $updatedData);
        $response->assertStatus(403);
    }

    /**
     * Test that users can delete their repositories.
     */
    public function test_users_can_delete_their_repositories(): void
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->delete(route('repositories.destroy', $repository));

        $this->assertDatabaseMissing('repositories', ['id' => $repository->id]);
        $response->assertSuccessful();
    }

    /**
     * Test that users cannot delete repositories they don't own.
     */
    public function test_users_cannot_delete_others_repositories(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $repository = Repository::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('repositories.destroy', $repository));

        $response->assertStatus(403);
        $this->assertDatabaseHas('repositories', ['id' => $repository->id]);
    }

    /**
     * Test that users can see repositories in their organizations.
     */
    public function test_users_can_see_repositories_in_their_organizations(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);
        $repository = Repository::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => null,
        ]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->get(route('repositories.show', $repository));

        $response->assertSuccessful();
    }
}
