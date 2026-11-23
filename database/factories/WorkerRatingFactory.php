<?php

namespace Database\Factories;

use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\WorkerRating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkerRating>
 */
class WorkerRatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_assignment_id' => TaskAssignment::factory(),
            'rated_by' => User::factory(),
            'quality_rating' => $this->faker->numberBetween(1, 5),
            'safety_rating' => $this->faker->numberBetween(1, 5),
            'professionalism_rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional()->sentence(),
            'rated_at' => now(),
        ];
    }
}
