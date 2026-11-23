<?php

namespace App\Services;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Enums\TaskAssignmentStatus;
use App\Enums\WorkTaskStatus;
use App\Models\TaskAssignment;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkerRating;
use App\Models\WorkOrder;
use App\Models\WorkTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkerRatingService
{
    public function __construct(private readonly AuditWriter $auditWriter) {}

    /** @param array{quality_rating: int|string, safety_rating: int|string, professionalism_rating: int|string, comment?: string|null} $attributes */
    public function rate(TaskAssignment $assignment, User $reviewer, array $attributes): WorkerRating
    {
        return DB::transaction(function () use ($assignment, $reviewer, $attributes): WorkerRating {
            $assignment = TaskAssignment::query()->lockForUpdate()->findOrFail($assignment->id);
            $task = WorkTask::query()->lockForUpdate()->findOrFail($assignment->work_task_id);
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($task->work_order_id);

            if (! $reviewer->can(PermissionName::FeedbackRateWorkers->value)
                || (! $reviewer->can(PermissionName::ReportsManageAll->value)
                    && ! UnitMembership::query()
                        ->where('user_id', $reviewer->id)
                        ->where('organization_unit_id', $workOrder->organization_unit_id)
                        ->where('membership_type', RoleName::Staff->value)
                        ->where('status', 'active')
                        ->whereNull('ended_at')
                        ->exists())) {
                throw ValidationException::withMessages(['taskAssignment' => 'You cannot rate a worker for this service unit.']);
            }

            if ($task->status !== WorkTaskStatus::Completed
                || $assignment->status !== TaskAssignmentStatus::Completed
                || $assignment->ended_at === null) {
                throw ValidationException::withMessages(['taskAssignment' => 'Worker feedback is available after staff verifies the completed task.']);
            }

            if ($assignment->worker_id === $reviewer->id) {
                throw ValidationException::withMessages(['taskAssignment' => 'Workers cannot rate their own assignments.']);
            }

            if ($assignment->workerRating()->exists()) {
                throw ValidationException::withMessages(['taskAssignment' => 'This completed assignment has already been rated.']);
            }

            $rating = $assignment->workerRating()->create([
                'rated_by' => $reviewer->id,
                'quality_rating' => (int) $attributes['quality_rating'],
                'safety_rating' => (int) $attributes['safety_rating'],
                'professionalism_rating' => (int) $attributes['professionalism_rating'],
                'comment' => isset($attributes['comment']) ? trim($attributes['comment']) : null,
                'rated_at' => now(),
            ]);

            $this->auditWriter->record('worker_rating.submitted', $rating, [
                'task_assignment_id' => $assignment->id,
                'quality_rating' => $rating->quality_rating,
                'safety_rating' => $rating->safety_rating,
                'professionalism_rating' => $rating->professionalism_rating,
            ], $reviewer);

            return $rating;
        });
    }
}
