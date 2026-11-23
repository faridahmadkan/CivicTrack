<?php

namespace App\Services;

use App\Enums\CivicReportStatus;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Enums\TaskAssignmentStatus;
use App\Enums\TaskVerificationDecision;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkTaskStatus;
use App\Events\CivicReportStatusChanged;
use App\Models\CivicReport;
use App\Models\ReportStatusEvent;
use App\Models\TaskAssignment;
use App\Models\TaskUpdate;
use App\Models\TaskVerification;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskVerificationService
{
    public function __construct(
        private readonly AuditWriter $auditWriter,
        private readonly ReportSlaService $reportSlaService,
    ) {}

    public function review(
        WorkTask $task,
        User $reviewer,
        TaskVerificationDecision $decision,
        ?string $reason,
    ): TaskVerification {
        return DB::transaction(function () use ($task, $reviewer, $decision, $reason): TaskVerification {
            $task = WorkTask::query()->lockForUpdate()->findOrFail($task->id);
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($task->work_order_id);
            $this->authorizeVerifier($workOrder, $reviewer);

            if ($task->status !== WorkTaskStatus::SubmittedForReview) {
                throw ValidationException::withMessages(['decision' => 'This task is not waiting for verification.']);
            }

            if ($decision === TaskVerificationDecision::NeedsRework && trim((string) $reason) === '') {
                throw ValidationException::withMessages(['reason' => 'Explain what needs to be corrected before returning the task.']);
            }

            $assignment = TaskAssignment::query()
                ->where('work_task_id', $task->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->first();

            if ($assignment === null || $assignment->status !== TaskAssignmentStatus::Submitted) {
                throw ValidationException::withMessages(['decision' => 'The submitted assignment is no longer active.']);
            }

            $verification = TaskVerification::query()->create([
                'work_task_id' => $task->id,
                'task_assignment_id' => $assignment->id,
                'reviewed_by' => $reviewer->id,
                'decision' => $decision,
                'reason' => $reason === null ? null : trim($reason),
                'reviewed_at' => now(),
            ]);

            $previousStatus = $task->status;
            $nextStatus = $decision === TaskVerificationDecision::Approved
                ? WorkTaskStatus::Completed
                : WorkTaskStatus::NeedsRework;
            $task->forceFill([
                'status' => $nextStatus,
                'verified_at' => now(),
                'completed_at' => $decision === TaskVerificationDecision::Approved ? now() : null,
            ])->save();

            $assignment->forceFill([
                'status' => $decision === TaskVerificationDecision::Approved
                    ? TaskAssignmentStatus::Completed
                    : TaskAssignmentStatus::NeedsRework,
                'ended_at' => $decision === TaskVerificationDecision::Approved ? now() : null,
                'end_reason' => $decision === TaskVerificationDecision::NeedsRework ? trim((string) $reason) : null,
            ])->save();

            TaskUpdate::query()->create([
                'work_task_id' => $task->id,
                'task_assignment_id' => $assignment->id,
                'actor_id' => $reviewer->id,
                'event_type' => $decision === TaskVerificationDecision::Approved ? 'verified' : 'rework_requested',
                'from_status' => $previousStatus->value,
                'to_status' => $nextStatus->value,
                'body' => $reason === null ? null : trim($reason),
                'occurred_at' => now(),
            ]);

            $this->auditWriter->record(
                'work_task.verification_recorded',
                $task,
                ['decision' => $decision->value, 'work_order_id' => $workOrder->id],
                $reviewer,
            );

            if ($decision === TaskVerificationDecision::Approved) {
                $this->completeWorkOrderIfReady($workOrder);
                $this->resolveReportIfAllWorkIsVerified($workOrder->civic_report_id, $reviewer);
            }

            return $verification->load(['task', 'assignment', 'reviewer']);
        });
    }

    private function completeWorkOrderIfReady(WorkOrder $workOrder): void
    {
        $hasIncompleteTask = WorkTask::query()
            ->where('work_order_id', $workOrder->id)
            ->where('status', '!=', WorkTaskStatus::Completed->value)
            ->exists();

        if (! $hasIncompleteTask) {
            $workOrder->forceFill([
                'status' => WorkOrderStatus::Completed,
                'completed_at' => now(),
            ])->save();
        }
    }

    private function resolveReportIfAllWorkIsVerified(int $reportId, User $reviewer): void
    {
        $hasIncompleteTask = WorkTask::query()
            ->whereHas('workOrder', static fn ($query) => $query->where('civic_report_id', $reportId))
            ->where('status', '!=', WorkTaskStatus::Completed->value)
            ->exists();

        if ($hasIncompleteTask) {
            return;
        }

        $report = CivicReport::query()->lockForUpdate()->findOrFail($reportId);

        if (! in_array($report->status, [CivicReportStatus::InProgress, CivicReportStatus::Acknowledged], true)) {
            return;
        }

        $fromStatus = $report->status;
        $report->forceFill([
            'status' => CivicReportStatus::Resolved,
            'resolved_at' => now(),
        ])->save();
        ReportStatusEvent::query()->create([
            'civic_report_id' => $report->id,
            'actor_id' => $reviewer->id,
            'event_type' => 'work_verified',
            'from_status' => $fromStatus->value,
            'to_status' => CivicReportStatus::Resolved->value,
            'citizen_visible' => true,
            'properties' => [],
            'occurred_at' => now(),
        ]);
        $this->reportSlaService->recordResolved($report, $reviewer, 'work_verified');
        $this->auditWriter->record('civic_report.resolved', $report, [], $reviewer);
        CivicReportStatusChanged::dispatch($report);
    }

    private function authorizeVerifier(WorkOrder $workOrder, User $reviewer): void
    {
        if ($reviewer->can(PermissionName::ReportsManageAll->value)) {
            return;
        }

        if (! $reviewer->can(PermissionName::VerificationManageDepartment->value)
            || ! UnitMembership::query()
                ->where('user_id', $reviewer->id)
                ->where('organization_unit_id', $workOrder->organization_unit_id)
                ->where('membership_type', RoleName::Staff->value)
                ->where('status', 'active')
                ->exists()) {
            throw ValidationException::withMessages(['decision' => 'You are not authorized to verify tasks for this service unit.']);
        }
    }
}
