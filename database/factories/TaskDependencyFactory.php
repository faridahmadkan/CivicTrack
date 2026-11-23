<?php

namespace Database\Factories;

use App\Models\WorkTask;
use App\Models\TaskDependency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskDependency>
 */
class TaskDependencyFactory extends Factory
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
            'depends_on_task_id' => WorkTask::factory(),
            'created_at' => now(),
        ];
    }
}
