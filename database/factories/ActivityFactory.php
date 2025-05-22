<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjectTypes = [
            \App\Models\Repository::class,
            \App\Models\Organization::class,
            null, // For activities without subjects
        ];
        
        $subjectType = $this->faker->randomElement($subjectTypes);
        
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => $this->faker->randomElement(['repository_created', 'repository_updated', 'organization_created']),
            'description' => $this->faker->sentence(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectType ? $subjectType::factory()->create()->id : null,
            'properties' => ['test' => 'property'],
        ];
    }
}
