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

    // Advanced Security & Privacy Tests
    public function test_explore_never_shows_private_repositories_even_with_url_manipulation()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        // Create only the repositories we control, no random ones from factories
        Repository::create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Public Repo',
            'slug' => 'public-repo',
            'description' => 'A safe public repository',
        ]);

        Repository::create([
            'user_id' => $user->id,
            'is_public' => false,
            'name' => 'UltraSecretProject',
            'slug' => 'ultra-secret-project',
            'description' => 'TopSecretCompanyProject',
        ]);

        Repository::create([
            'user_id' => $authUser->id,
            'is_public' => false,
            'name' => 'MyPrivateRepo',
            'slug' => 'my-private-repo',
            'description' => 'Very private repository',
        ]);

        // Verify our data setup first
        $this->assertEquals(1, Repository::where('is_public', true)->count());
        $this->assertEquals(2, Repository::where('is_public', false)->count());
        $this->assertEquals(1, Repository::public()->count());

        // Test basic explore page (should only show public repo)
        $response = $this->actingAs($authUser)->get(route('explore'));
        $response->assertStatus(200);
        $response->assertSee('Public Repo');
        $response->assertDontSee('UltraSecretProject');
        $response->assertDontSee('MyPrivateRepo');

        // Test searches that would find private repos if they were public
        $response = $this->actingAs($authUser)->get(route('explore', ['search' => 'UltraSecret']));
        $response->assertStatus(200);
        $response->assertDontSee('UltraSecretProject');
        $response->assertSee('No repositories found');

        $response = $this->actingAs($authUser)->get(route('explore', ['search' => 'MyPrivate']));
        $response->assertStatus(200);
        $response->assertDontSee('MyPrivateRepo');
        $response->assertSee('No repositories found');
    }

    public function test_explore_requires_authentication()
    {
        Repository::factory()->create(['is_public' => true]);

        $response = $this->get(route('explore'));

        $response->assertRedirect(route('login'));
    }

    public function test_explore_shows_correct_repository_count_with_mixed_visibility()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        // Create mix of public and private repositories
        Repository::factory()->count(5)->create([
            'user_id' => $user->id,
            'is_public' => true,
        ]);

        Repository::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($authUser)->get(route('explore'));

        $response->assertStatus(200);
        $response->assertSee('5 public repositories');
        $response->assertDontSee('8 public repositories');
        $response->assertDontSee('3 public repositories');
    }

    // Edge Cases & Input Validation Tests
    public function test_explore_search_handles_special_characters_safely()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'C++ Project',
            'slug' => 'cpp-project',
            'description' => 'A C++ library for data structures & algorithms',
        ]);

        $specialCharSearches = [
            'C++',
            'data structures & algorithms',
            '@#$%^&*',
            '<script>alert("xss")</script>',
            "'; DROP TABLE repositories; --",
            '%',
            '_',
            '\\',
        ];

        foreach ($specialCharSearches as $search) {
            $response = $this->actingAs($authUser)->get(route('explore', ['search' => $search]));
            $response->assertStatus(200);

            // Should find C++ project when searching for C++
            if ($search === 'C++') {
                $response->assertSee('C++ Project');
            }

            // Should find the project when searching for part of description
            if (str_contains($search, 'data structures')) {
                $response->assertSee('C++ Project');
            }
        }
    }

    public function test_explore_search_is_case_insensitive()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Laravel Framework',
            'slug' => 'laravel-framework',
            'description' => 'PHP Web Framework',
        ]);

        $searches = ['laravel', 'LARAVEL', 'Laravel', 'php', 'PHP', 'Php', 'FRAMEWORK'];

        foreach ($searches as $search) {
            $response = $this->actingAs($authUser)->get(route('explore', ['search' => $search]));
            $response->assertStatus(200);
            $response->assertSee('Laravel Framework');
        }
    }

    public function test_explore_handles_repositories_with_null_descriptions()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'No Description Repo',
            'slug' => 'no-description-repo',
            'description' => null,
        ]);

        $response = $this->actingAs($authUser)->get(route('explore'));

        $response->assertStatus(200);
        $response->assertSee('No Description Repo');
        // Should not crash when rendering null description
    }

    public function test_explore_pagination_handles_edge_cases()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        // Create exactly 12 repositories (pagination limit)
        Repository::factory()->count(12)->create([
            'user_id' => $user->id,
            'is_public' => true,
        ]);

        // Test first page
        $response = $this->actingAs($authUser)->get(route('explore', ['page' => 1]));
        $response->assertStatus(200);
        $response->assertDontSee('Previous');

        // Test invalid page numbers
        $invalidPages = [0, -1, 999, 'abc', ''];
        foreach ($invalidPages as $page) {
            $response = $this->actingAs($authUser)->get(route('explore', ['page' => $page]));
            $response->assertStatus(200); // Should handle gracefully
        }
    }

    // Complex Filtering & Sorting Tests
    public function test_explore_combined_filters_work_correctly()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);

        // Create test data
        Repository::factory()->create([
            'user_id' => $user->id,
            'organization_id' => null,
            'is_public' => true,
            'name' => 'Laravel Personal Project',
            'slug' => 'laravel-personal',
            'created_at' => now()->subDays(1),
        ]);

        Repository::factory()->create([
            'user_id' => null,
            'organization_id' => $organization->id,
            'is_public' => true,
            'name' => 'Laravel Org Project',
            'slug' => 'laravel-org',
            'created_at' => now()->subDays(2),
        ]);

        Repository::factory()->create([
            'user_id' => $user->id,
            'organization_id' => null,
            'is_public' => true,
            'name' => 'React Personal Project',
            'slug' => 'react-personal',
            'created_at' => now()->subDays(3),
        ]);

        // Test search + owner type filter
        $response = $this->actingAs($authUser)->get(route('explore', [
            'search' => 'Laravel',
            'owner_type' => 'user',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Laravel Personal Project');
        $response->assertDontSee('Laravel Org Project');
        $response->assertDontSee('React Personal Project');

        // Test search + owner type + sort
        $response = $this->actingAs($authUser)->get(route('explore', [
            'search' => 'Laravel',
            'owner_type' => 'organization',
            'sort' => 'oldest',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Laravel Org Project');
        $response->assertDontSee('Laravel Personal Project');
    }

    public function test_explore_sort_by_name_works_alphabetically()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Zebra Project',
            'slug' => 'zebra-project',
        ]);

        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Alpha Project',
            'slug' => 'alpha-project',
        ]);

        Repository::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Beta Project',
            'slug' => 'beta-project',
        ]);

        // Test A-Z sorting
        $response = $this->actingAs($authUser)->get(route('explore', ['sort' => 'name']));
        $content = $response->getContent();

        $alphaPos = strpos($content, 'Alpha Project');
        $betaPos = strpos($content, 'Beta Project');
        $zebraPos = strpos($content, 'Zebra Project');

        $this->assertTrue($alphaPos < $betaPos);
        $this->assertTrue($betaPos < $zebraPos);

        // Test Z-A sorting
        $response = $this->actingAs($authUser)->get(route('explore', ['sort' => 'name_desc']));
        $content = $response->getContent();

        $alphaPos = strpos($content, 'Alpha Project');
        $betaPos = strpos($content, 'Beta Project');
        $zebraPos = strpos($content, 'Zebra Project');

        $this->assertTrue($zebraPos < $betaPos);
        $this->assertTrue($betaPos < $alphaPos);
    }

    // Data Integrity & Relationship Tests
    public function test_explore_handles_repositories_with_missing_owner_gracefully()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        // Create a repository with a valid user, then simulate deletion by removing relationships
        $repo = Repository::factory()->create([
            'user_id' => $user->id,
            'organization_id' => null,
            'is_public' => true,
            'name' => 'Orphaned Repo',
            'slug' => 'orphaned-repo',
        ]);

        // Simulate user deletion by setting user_id to null (simulating cascade behavior)
        $repo->update(['user_id' => null]);

        $response = $this->actingAs($authUser)->get(route('explore'));

        // Should not crash, but may or may not show the repo depending on implementation
        $response->assertStatus(200);
    }

    public function test_explore_preserves_filters_across_pagination()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        // Create 15 user repositories with searchable names
        Repository::factory()->count(15)->create([
            'user_id' => $user->id,
            'organization_id' => null,
            'is_public' => true,
            'name' => function () {
                return 'Laravel Project '.rand(1000, 9999);
            },
        ]);

        $response = $this->actingAs($authUser)->get(route('explore', [
            'search' => 'Laravel',
            'owner_type' => 'user',
            'sort' => 'latest',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        // Check that pagination links preserve the filters
        $content = $response->getContent();
        $this->assertStringContainsString('search=Laravel', $content);
        $this->assertStringContainsString('owner_type=user', $content);
        $this->assertStringContainsString('sort=latest', $content);
    }

    // Performance & Load Tests
    public function test_explore_performs_efficiently_with_large_dataset()
    {
        $authUser = User::factory()->create();
        $users = User::factory()->count(10)->create();

        // Create 100 repositories across multiple users
        foreach ($users as $user) {
            Repository::factory()->count(10)->create([
                'user_id' => $user->id,
                'is_public' => true,
            ]);
        }

        $startTime = microtime(true);

        $response = $this->actingAs($authUser)->get(route('explore'));

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        // Should complete within reasonable time (2 seconds)
        $this->assertLessThan(2.0, $executionTime, 'Explore page should load quickly even with many repositories');
    }

    // Business Logic Tests
    public function test_explore_shows_correct_owner_information_for_transferred_repositories()
    {
        $authUser = User::factory()->create();
        $originalOwner = User::factory()->create(['name' => 'Original Owner']);
        $organization = Organization::factory()->create([
            'owner_id' => $originalOwner->id,
            'name' => 'Test Organization',
        ]);

        // Create repository originally owned by user, then transferred to organization
        $repository = Repository::factory()->create([
            'user_id' => null,
            'organization_id' => $organization->id,
            'is_public' => true,
            'name' => 'Transferred Repo',
            'slug' => 'transferred-repo',
        ]);

        $response = $this->actingAs($authUser)->get(route('explore'));

        $response->assertStatus(200);
        $response->assertSee('Transferred Repo');
        $response->assertSee('Test Organization');
        $response->assertDontSee('Original Owner'); // Should show org, not original user
    }

    public function test_explore_empty_search_returns_all_public_repositories()
    {
        $authUser = User::factory()->create();
        $user = User::factory()->create();

        Repository::factory()->count(5)->create([
            'user_id' => $user->id,
            'is_public' => true,
        ]);

        $emptySearchValues = ['', '   ', null];

        foreach ($emptySearchValues as $search) {
            $response = $this->actingAs($authUser)->get(route('explore', ['search' => $search]));
            $response->assertStatus(200);
            $response->assertSee('5 public repositories');
        }
    }
}
