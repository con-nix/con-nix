<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_transfer_form_for_owned_repository()
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('repositories.transfer.form', $repository));

        $response->assertStatus(200);
        $response->assertSee('Transfer Repository');
        $response->assertSee($repository->name);
    }

    public function test_user_cannot_view_transfer_form_for_unowned_repository()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $repository = Repository::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)
            ->get(route('repositories.transfer.form', $repository));

        $response->assertStatus(403);
    }

    public function test_user_can_transfer_repository_to_personal_account()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);
        $repository = Repository::factory()->create(['organization_id' => $organization->id]);

        $response = $this->actingAs($user)
            ->patch(route('repositories.transfer', $repository), [
                'transfer_type' => 'user',
                'confirmation' => $repository->name,
            ]);

        $response->assertRedirect(route('repositories.show', $repository));
        $response->assertSessionHas('status', 'Repository transferred to your personal account successfully.');

        $repository->refresh();
        $this->assertEquals($user->id, $repository->user_id);
        $this->assertNull($repository->organization_id);
    }

    public function test_user_can_transfer_repository_to_organization()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $user->id]);
        $repository = Repository::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->patch(route('repositories.transfer', $repository), [
                'transfer_type' => 'organization',
                'organization_id' => $organization->id,
                'confirmation' => $repository->name,
            ]);

        $response->assertRedirect(route('repositories.show', $repository));
        $response->assertSessionHas('status', "Repository transferred to {$organization->name} successfully.");

        $repository->refresh();
        $this->assertEquals($organization->id, $repository->organization_id);
        $this->assertNull($repository->user_id);
    }

    public function test_transfer_fails_with_incorrect_confirmation()
    {
        $user = User::factory()->create();
        $repository = Repository::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->patch(route('repositories.transfer', $repository), [
                'transfer_type' => 'user',
                'confirmation' => 'wrong-name',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['confirmation']);

        $repository->refresh();
        $this->assertEquals($user->id, $repository->user_id);
    }

    public function test_user_cannot_transfer_to_organization_they_dont_own()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $organization = Organization::factory()->create(['owner_id' => $otherUser->id]);
        $repository = Repository::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->patch(route('repositories.transfer', $repository), [
                'transfer_type' => 'organization',
                'organization_id' => $organization->id,
                'confirmation' => $repository->name,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['organization_id']);

        $repository->refresh();
        $this->assertEquals($user->id, $repository->user_id);
    }
}
