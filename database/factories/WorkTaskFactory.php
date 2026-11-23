<?php

namespace Database\Factories;

use App\Enums\CivicReportPriority;
use App\Enums\WorkTaskStatus;
use App\Models\WorkTask;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\WorkOrder;

/**
 * @extends Factory<WorkTask>
 */
class WorkTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'task_number' => 'CT-TK-'.Str::upper((string) Str::ulid()),
            'title' => $this->faker->sentence(5),
            'instructions' => $this->faker->paragraph(),
            'status' => WorkTaskStatus::Queued,
            'priority' => CivicReportPriority::Normal,
            'sequence' => 1,
        ];
    }
}
