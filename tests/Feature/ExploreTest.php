<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreTest extends TestCase
{
    use RefreshDatabase;

    public function test_explore_page_shows_public_repositories()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Create public repositories
        $publicRepo1 = Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Public Repo 1',
            'description' => 'This is a public repository',
        ]);

        $publicRepo2 = Repository::factory()->create([
            'user_id' => $otherUser->id,
            'is_public' => true,
            'name' => 'Public Repo 2',
        ]);

        // Create private repository (should not appear)
        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => false,
            'name' => 'Private Repo',
        ]);

        $response = $this->actingAs($authUser)->get(route('explore'));

        $response->assertStatus(200);
        $response->assertSee('Explore Repositories');
        $response->assertSee('Public Repo 1');
        $response->assertSee('Public Repo 2');
        $response->assertDontSee('Private Repo');
        $response->assertSee('2 public repositories');
    }

    public function test_explore_search_functionality()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Laravel Project',
            'slug' => 'laravel-project',
            'description' => 'A web application built with Laravel',
        ]);

        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'React App',
            'slug' => 'react-app',
            'description' => 'Frontend application using React',
        ]);

        // Search by name
        $response = $this->actingAs($authUser)->get(route('explore', ['search' => 'Laravel']));
        $response->assertStatus(200);
        $response->assertSee('Laravel Project');
        $response->assertDontSee('React App');

        // Search by description
        $response = $this->actingAs($authUser)->get(route('explore', ['search' => 'Frontend']));
        $response->assertStatus(200);
        $response->assertSee('React App');
        $response->assertDontSee('Laravel Project');
    }

    public function test_explore_filters_by_owner_type()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        // Personal repository
        $personalRepo = Repository::factory()->create([
            'user_id' => $user->id,
            'organization_id' => null,
            'is_public' => true,
            'name' => 'Personal Repo',
            'slug' => 'personal-repo',
        ]);

        // Organization repository
        $orgRepo = Repository::factory()->create([
            'user_id' => null,
            'organization_id' => $organization->id,
            'is_public' => true,
            'name' => 'Org Repo',
            'slug' => 'org-repo',
        ]);

        // Filter by user repositories
        $response = $this->actingAs($authUser)->get(route('explore', ['owner_type' => 'user']));
        $response->assertStatus(200);
        $response->assertSee('Personal Repo');
        $response->assertDontSee('Org Repo');

        // Filter by organization repositories
        $response = $this->actingAs($authUser)->get(route('explore', ['owner_type' => 'organization']));
        $response->assertStatus(200);
        $response->assertSee('Org Repo');
        $response->assertDontSee('Personal Repo');
    }

    public function test_explore_sorting_functionality()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        // Create repositories with different timestamps
        $oldRepo = Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Old Repo',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        $newRepo = Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'New Repo',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        // Test latest sorting (default)
        $response = $this->actingAs($authUser)->get(route('explore', ['sort' => 'latest']));
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('New Repo', $content);
        $this->assertStringContainsString('Old Repo', $content);

        // Test name sorting
        $response = $this->actingAs($authUser)->get(route('explore', ['sort' => 'name']));
        $response->assertStatus(200);
    }

    public function test_explore_pagination_works()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        // Create more than 12 repositories (pagination limit)
        Repository::factory()->count(15)->create([
            'user_id' => $user->id,
            'is_public' => true,
        ]);

        $response = $this->actingAs($authUser)->get(route('explore'));
        $response->assertStatus(200);

        // Should show pagination links
        $response->assertSee('Next');
    }

    public function test_explore_shows_repository_owner_information()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create(['name' => 'John Doe']);
        $organization = Organization::factory()->create([
            'owner_id' => $user->id,
            'name' => 'My Organization',
        ]);

        // Personal repository
        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Personal Repo',
        ]);

        // Organization repository
        Repository::factory()->create([
            'organization_id' => $organization->id,
            'is_public' => true,
            'name' => 'Org Repo',
        ]);

        $response = $this->actingAs($authUser)->get(route('explore'));
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('My Organization');
    }

    public function test_explore_shows_empty_state_when_no_repositories()
    {
        $authUser = User::factory()->create();

        $response = $this->actingAs($authUser)->get(route('explore'));

        $response->assertStatus(200);
        $response->assertSee('No repositories found');
        $response->assertSee('There are no public repositories to explore yet');
    }

    public function test_explore_clear_filters_link_works()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();
        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Test Repo',
        ]);

        $response = $this->actingAs($authUser)->get(route('explore', ['search' => 'test', 'owner_type' => 'user']));
        $response->assertStatus(200);
        $response->assertSee('Clear filters');
    }
}
