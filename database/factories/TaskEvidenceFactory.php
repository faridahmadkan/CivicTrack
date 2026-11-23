<?php

namespace Database\Factories;

use App\Models\TaskAssignment;
use App\Models\TaskEvidence;
use App\Models\User;
use App\Models\WorkTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskEvidence>
 */
class TaskEvidenceFactory extends Factory
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
            'uploaded_by' => User::factory(),
            'storage_disk' => 'private',
            'storage_path' => 'task-evidence/factory/evidence.jpg',
            'original_name' => 'evidence.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1024,
            'sha256' => hash('sha256', 'factory-task-evidence'),
            'evidence_type' => 'progress',
        ];
    }
}
