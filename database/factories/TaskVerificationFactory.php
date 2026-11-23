<?php

namespace Database\Factories;

use App\Enums\TaskVerificationDecision;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\WorkTask;
use App\Models\TaskVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskVerification>
 */
class TaskVerificationFactory extends Factory
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
            'reviewed_by' => User::factory(),
            'decision' => TaskVerificationDecision::Approved,
            'reviewed_at' => now(),
        ];
    }
}
