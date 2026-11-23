<?php

namespace Database\Factories;

use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\WorkTask;
use App\Models\TaskUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskUpdate>
 */
class TaskUpdateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_task_id' => WorkTask::factory(),
            'task_assignment_id' => TaskAssignment::factory(),
            'actor_id' => User::factory(),
            'event_type' => 'progress_update',
            'body' => $this->faker->sentence(),
            'occurred_at' => now(),
        ];
    }
}
