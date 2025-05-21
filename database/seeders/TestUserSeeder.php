<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create an organization
        $organization = \App\Models\Organization::create([
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'description' => 'This is a test organization',
            'owner_id' => 1,
        ]);

        // Create a personal repository for the user
        \App\Models\Repository::create([
            'name' => 'Personal Test Repo',
            'slug' => 'personal-test-repo',
            'description' => 'This is a personal test repository',
            'is_public' => true,
            'user_id' => 1,
        ]);

        // Create an organization repository
        \App\Models\Repository::create([
            'name' => 'Organization Test Repo',
            'slug' => 'organization-test-repo',
            'description' => 'This is an organization test repository',
            'is_public' => true,
            'organization_id' => $organization->id,
        ]);
    }
}
