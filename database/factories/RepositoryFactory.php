<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repository>
 */
class RepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name.'-'.$this->faker->randomNumber(4)),
            'description' => $this->faker->paragraph(),
            'is_public' => $this->faker->boolean(80), // 80% chance of being public
            'user_id' => \App\Models\User::factory(),
            'organization_id' => null,
        ];
    }

    /**
     * Define a state for repositories that belong to an organization.
     */
    public function inOrganization(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
                'organization_id' => \App\Models\Organization::factory(),
            ];
        });
    }
}
