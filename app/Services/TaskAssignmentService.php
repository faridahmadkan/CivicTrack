<?php

namespace App\Services;

use App\Enums\CivicReportStatus;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Enums\TaskAssignmentStatus;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkTaskStatus;
use App\Events\CivicReportStatusChanged;
use App\Events\TaskAssigned;
use App\Models\CivicReport;
use App\Models\ReportStatusEvent;
use App\Models\TaskAssignment;
use App\Models\TaskEvidence;
use App\Models\TaskUpdate;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class TaskAssignmentService
{
    public function __construct(private readonly AuditWriter $auditWriter) {}

    public function assign(WorkTask $task, User $worker, User $actor, ?string $instructions, ?string $reason): TaskAssignment
    {
        $assignment = DB::transaction(function () use ($task, $worker, $actor, $instructions, $reason): TaskAssignment {
            $task = WorkTask::query()->lockForUpdate()->findOrFail($task->id);
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($task->work_order_id);
            $this->authorizeDepartmentAccess($workOrder, $actor);

            if ($workOrder->status !== WorkOrderStatus::Open) {
                throw ValidationException::withMessages(['workTask' => 'Tasks in a completed or cancelled work order cannot be assigned.']);
            }

            if (! in_array($task->status, [
                WorkTaskStatus::Queued,
                WorkTaskStatus::Assigned,
                WorkTaskStatus::InProgress,
                WorkTaskStatus::NeedsRework,
            ], true)) {
                throw ValidationException::withMessages(['workTask' => 'This task is not available for assignment.']);
            }

            $currentAssignment = TaskAssignment::query()
                ->where('work_task_id', $task->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->first();

            if ($currentAssignment !== null) {
                if (trim((string) $reason) === '') {
                    throw ValidationException::withMessages(['reason' => 'A reason is required when changing an active assignment.']);
                }

                if ($currentAssignment->status === TaskAssignmentStatus::Submitted) {
                    throw ValidationException::withMessages(['workTask' => 'A submitted task must be verified before it can be reassigned.']);
                }

                if ($currentAssignment->worker_id === $worker->id) {
                    throw ValidationException::withMessages(['worker_id' => 'Choose a different worker for this reassignment.']);
                }

                $currentAssignment->forceFill([
                    'status' => TaskAssignmentStatus::Reassigned,
                    'ended_at' => now(),
                    'end_reason' => trim((string) $reason),
                ])->save();
            }

            $worker = User::query()->lockForUpdate()->findOrFail($worker->id);
            $this->authorizeWorker($worker, $workOrder->organization_unit_id, $task->id);
            $this->assertDependenciesComplete($task);

            $task->forceFill(['status' => WorkTaskStatus::Assigned])->save();
            $assignment = $task->assignments()->create([
                'worker_id' => $worker->id,
                'assigned_by' => $actor->id,
                'status' => TaskAssignmentStatus::Assigned,
                'assignment_instructions' => $instructions === null ? null : trim($instructions),
                'assigned_at' => now(),
            ]);

            $this->recordTaskUpdate($task, $assignment, $actor, 'assigned', null, WorkTaskStatus::Assigned, $reason);
            $this->auditWriter->record(
                'work_task.assigned',
                $task,
                ['work_order_id' => $workOrder->id, 'worker_id' => $worker->id],
                $actor,
            );

            $report = CivicReport::query()->lockForUpdate()->findOrFail($workOrder->civic_report_id);

            if ($report->status === CivicReportStatus::Acknowledged) {
                $report->forceFill(['status' => CivicReportStatus::InProgress])->save();
                ReportStatusEvent::query()->create([
                    'civic_report_id' => $report->id,
                    'actor_id' => $actor->id,
                    'event_type' => 'work_started',
                    'from_status' => CivicReportStatus::Acknowledged->value,
                    'to_status' => CivicReportStatus::InProgress->value,
                    'citizen_visible' => true,
                    'properties' => ['work_order_id' => $workOrder->id],
                    'occurred_at' => now(),
                ]);
                $this->auditWriter->record('civic_report.work_started', $report, ['work_order_id' => $workOrder->id], $actor);
                CivicReportStatusChanged::dispatch($report);
            }

            return $assignment->load(['task.workOrder.report', 'worker']);
        });

        TaskAssigned::dispatch($assignment);

        return $assignment;
    }

    public function accept(WorkTask $task, User $worker): TaskAssignment
    {
        return DB::transaction(function () use ($task, $worker): TaskAssignment {
            $task = WorkTask::query()->lockForUpdate()->findOrFail($task->id);
            $assignment = $this->activeAssignmentForWorker($task, $worker, true);

            if ($assignment->status !== TaskAssignmentStatus::Assigned) {
                throw ValidationException::withMessages(['assignment' => 'This assignment is no longer waiting for your response.']);
            }

            $assignment->forceFill([
                'status' => TaskAssignmentStatus::Accepted,
                'accepted_at' => now(),
            ])->save();
            $this->recordTaskUpdate($task, $assignment, $worker, 'accepted', $task->status, $task->status, null);

            return $assignment->fresh(['task.workOrder.report']);
        });
    }

    public function decline(WorkTask $task, User $worker, string $reason): TaskAssignment
    {
        return DB::transaction(function () use ($task, $worker, $reason): TaskAssignment {
            $task = WorkTask::query()->lockForUpdate()->findOrFail($task->id);
            $assignment = $this->activeAssignmentForWorker($task, $worker, true);

            if ($assignment->status !== TaskAssignmentStatus::Assigned) {
                throw ValidationException::withMessages(['assignment' => 'Only a newly assigned task can be declined.']);
            }

            $previousStatus = $task->status;
            $assignment->forceFill([
                'status' => TaskAssignmentStatus::Declined,
                'ended_at' => now(),
                'end_reason' => trim($reason),
            ])->save();
            $task->forceFill(['status' => WorkTaskStatus::Queued])->save();
            $this->recordTaskUpdate($task, $assignment, $worker, 'declined', $previousStatus, WorkTaskStatus::Queued, trim($reason));
            $this->auditWriter->record('work_task.assignment_declined', $task, ['reason' => trim($reason)], $worker);

            return $assignment->fresh(['task.workOrder.report']);
        });
    }

    public function start(WorkTask $task, User $worker): TaskAssignment
    {
        return DB::transaction(function () use ($task, $worker): TaskAssignment {
            $task = WorkTask::query()->lockForUpdate()->findOrFail($task->id);
            $assignment = $this->activeAssignmentForWorker($task, $worker, true);

            if (! in_array($assignment->status, [TaskAssignmentStatus::Accepted, TaskAssignmentStatus::NeedsRework], true)) {
                throw ValidationException::withMessages(['assignment' => 'Accept the assignment before starting work.']);
            }

            $this->assertDependenciesComplete($task);
            $previousStatus = $task->status;
            $task->forceFill([
                'status' => WorkTaskStatus::InProgress,
                'started_at' => $task->started_at ?? now(),
            ])->save();
            $assignment->forceFill([
                'status' => TaskAssignmentStatus::InProgress,
                'started_at' => $assignment->started_at ?? now(),
            ])->save();
            $this->recordTaskUpdate($task, $assignment, $worker, 'started', $previousStatus, WorkTaskStatus::InProgress, null);

            return $assignment->fresh(['task.workOrder.report']);
        });
    }

    public function addProgressUpdate(WorkTask $task, User $worker, string $body): TaskUpdate
    {
        $assignment = $this->activeAssignmentForWorker($task, $worker);

        if (! in_array($assignment->status, [TaskAssignmentStatus::InProgress, TaskAssignmentStatus::NeedsRework], true)) {
            throw ValidationException::withMessages(['body' => 'Start work before adding a progress update.']);
        }

        $update = $this->recordTaskUpdate($task, $assignment, $worker, 'progress_update', $task->status, $task->status, trim($body));
        $this->auditWriter->record('work_task.progress_updated', $task, [], $worker);

        return $update;
    }

    /** @param array<int, UploadedFile> $files */
    public function addEvidence(WorkTask $task, User $worker, array $files, string $evidenceType, ?string $caption): void
    {
        $storedPaths = [];

        try {
            DB::transaction(function () use ($task, $worker, $files, $evidenceType, $caption, &$storedPaths): void {
                $task = WorkTask::query()->lockForUpdate()->findOrFail($task->id);
                $assignment = $this->activeAssignmentForWorker($task, $worker, true);

                if (! in_array($assignment->status, [TaskAssignmentStatus::InProgress, TaskAssignmentStatus::NeedsRework], true)) {
                    throw ValidationException::withMessages(['evidence' => 'Evidence can be added while work is in progress or being corrected.']);
                }

                foreach ($files as $file) {
                    $this->storeEvidence($task, $assignment, $worker, $file, $evidenceType, $caption, $storedPaths);
                }

                $this->recordTaskUpdate($task, $assignment, $worker, 'evidence_added', $task->status, $task->status, $caption);
                $this->auditWriter->record('work_task.evidence_added', $task, ['file_count' => count($files)], $worker);
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }

            throw $exception;
        }
    }

    public function submitForVerification(WorkTask $task, User $worker): TaskAssignment
    {
        return DB::transaction(function () use ($task, $worker): TaskAssignment {
            $task = WorkTask::query()->lockForUpdate()->findOrFail($task->id);
            $assignment = $this->activeAssignmentForWorker($task, $worker, true);

            if (! in_array($assignment->status, [TaskAssignmentStatus::InProgress, TaskAssignmentStatus::NeedsRework], true)) {
                throw ValidationException::withMessages(['assignment' => 'This task cannot be submitted for verification in its current state.']);
            }

            if (! $task->evidence()->where('task_assignment_id', $assignment->id)->exists()) {
                throw ValidationException::withMessages(['evidence' => 'Add at least one piece of work evidence before submitting for verification.']);
            }

            $previousStatus = $task->status;
            $task->forceFill([
                'status' => WorkTaskStatus::SubmittedForReview,
                'submitted_at' => now(),
            ])->save();
            $assignment->forceFill(['status' => TaskAssignmentStatus::Submitted])->save();
            $this->recordTaskUpdate(
                $task,
                $assignment,
                $worker,
                'submitted_for_verification',
                $previousStatus,
                WorkTaskStatus::SubmittedForReview,
                null,
            );
            $this->auditWriter->record('work_task.submitted_for_verification', $task, [], $worker);

            return $assignment->fresh(['task.workOrder.report']);
        });
    }

    private function authorizeWorker(User $worker, int $organizationUnitId, int $currentTaskId): void
    {
        if (! $worker->isActive()
            || ! $worker->hasRole(RoleName::Worker->value)
            || ! $worker->workerProfile()->exists()
            || ! UnitMembership::query()
                ->where('user_id', $worker->id)
                ->where('organization_unit_id', $organizationUnitId)
                ->where('membership_type', RoleName::Worker->value)
                ->where('status', 'active')
                ->lockForUpdate()
                ->exists()) {
            throw ValidationException::withMessages(['worker_id' => 'Choose an approved worker with an active membership in this service unit.']);
        }

        $activeTaskLimit = (int) config('civictrack.workforce.active_task_limit', 8);
        $activeTaskCount = TaskAssignment::query()
            ->where('worker_id', $worker->id)
            ->where('work_task_id', '!=', $currentTaskId)
            ->whereNull('ended_at')
            ->whereIn('status', [
                TaskAssignmentStatus::Assigned->value,
                TaskAssignmentStatus::Accepted->value,
                TaskAssignmentStatus::InProgress->value,
                TaskAssignmentStatus::NeedsRework->value,
            ])
            ->count();

        if ($activeTaskCount >= max(1, $activeTaskLimit)) {
            throw ValidationException::withMessages(['worker_id' => 'This worker has reached the active task limit.']);
        }
    }

    private function authorizeDepartmentAccess(WorkOrder $workOrder, User $actor): void
    {
        if ($actor->can(PermissionName::ReportsManageAll->value)) {
            return;
        }

        if (! $actor->can(PermissionName::AssignmentsManageDepartment->value)
            || ! UnitMembership::query()
                ->where('user_id', $actor->id)
                ->where('organization_unit_id', $workOrder->organization_unit_id)
                ->where('membership_type', RoleName::Staff->value)
                ->where('status', 'active')
                ->exists()) {
            throw ValidationException::withMessages(['workTask' => 'You are not authorized to manage this task assignment.']);
        }
    }

    private function assertDependenciesComplete(WorkTask $task): void
    {
        $hasIncompleteDependency = $task->dependencies()
            ->where('work_tasks.status', '!=', WorkTaskStatus::Completed->value)
            ->exists();

        if ($hasIncompleteDependency) {
            throw ValidationException::withMessages(['workTask' => 'Complete prerequisite tasks before assigning or starting this task.']);
        }
    }

    private function activeAssignmentForWorker(WorkTask $task, User $worker, bool $lock = false): TaskAssignment
    {
        $query = TaskAssignment::query()
            ->where('work_task_id', $task->id)
            ->where('worker_id', $worker->id)
            ->whereNull('ended_at');

        if ($lock) {
            $query->lockForUpdate();
        }

        $assignment = $query->first();

        if ($assignment === null) {
            throw ValidationException::withMessages(['workTask' => 'This task is not assigned to your account.']);
        }

        return $assignment;
    }

    /** @param array<int, string> $storedPaths */
    private function storeEvidence(
        WorkTask $task,
        TaskAssignment $assignment,
        User $worker,
        UploadedFile $file,
        string $evidenceType,
        ?string $caption,
        array &$storedPaths,
    ): TaskEvidence {
        $path = $file->store('task-evidence/'.$task->id, 'local');

        if ($path === false) {
            throw new RuntimeException('The task evidence could not be stored.');
        }

        $storedPaths[] = $path;
        $hash = hash_file('sha256', $file->getRealPath());

        if ($hash === false) {
            throw new RuntimeException('The task evidence could not be verified.');
        }

        $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', basename($file->getClientOriginalName())) ?: 'evidence';

        return $task->evidence()->create([
            'task_assignment_id' => $assignment->id,
            'uploaded_by' => $worker->id,
            'storage_disk' => 'local',
            'storage_path' => $path,
            'original_name' => \Illuminate\Support\Str::limit($originalName, 180, ''),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => $file->getSize() ?? 0,
            'sha256' => $hash,
            'evidence_type' => $evidenceType,
            'caption' => $caption === null ? null : trim($caption),
        ]);
    }

    private function recordTaskUpdate(
        WorkTask $task,
        TaskAssignment $assignment,
        User $actor,
        string $eventType,
        ?WorkTaskStatus $fromStatus,
        ?WorkTaskStatus $toStatus,
        ?string $body,
    ): TaskUpdate {
        return TaskUpdate::query()->create([
            'work_task_id' => $task->id,
            'task_assignment_id' => $assignment->id,
            'actor_id' => $actor->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus?->value,
            'to_status' => $toStatus?->value,
            'body' => $body === null ? null : trim($body),
            'occurred_at' => now(),
        ]);
    }
}
