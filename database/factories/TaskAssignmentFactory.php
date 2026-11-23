<?php

namespace Database\Factories;

use App\Enums\TaskAssignmentStatus;
use App\Models\User;
use App\Models\WorkTask;
use App\Models\TaskAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskAssignment>
 */
class TaskAssignmentFactory extends Factory
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
            'worker_id' => User::factory(),
            'status' => TaskAssignmentStatus::Assigned,
            'assigned_at' => now(),
        ];
    }
}
