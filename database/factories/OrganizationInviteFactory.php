<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganizationInvite>
 */
class OrganizationInviteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => \App\Models\Organization::factory(),
            'sender_id' => \App\Models\User::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => $this->faker->randomElement(['admin', 'member', 'viewer']),
            'token' => \Illuminate\Support\Str::random(64),
            'expires_at' => \Carbon\Carbon::now()->addDays(7),
            'accepted_at' => null,
        ];
    }
}
