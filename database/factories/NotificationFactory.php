<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => $this->faker->randomElement(['user_follow', 'organization_invite', 'repository_update']),
            'title' => $this->faker->sentence(),
            'message' => $this->faker->sentence(),
            'data' => ['test' => 'data'],
            'action_url' => $this->faker->optional()->url(),
            'read_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
