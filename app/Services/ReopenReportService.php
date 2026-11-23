<?php

namespace App\Services;

use App\Enums\CivicReportStatus;
use App\Enums\PermissionName;
use App\Enums\ReportReopenStatus;
use App\Enums\RoleName;
use App\Events\CivicReportStatusChanged;
use App\Models\CivicReport;
use App\Models\ReportReopenRequest;
use App\Models\ReportStatusEvent;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReopenReportService
{
    public function __construct(
        private readonly AuditWriter $auditWriter,
        private readonly ReportSlaService $reportSlaService,
    ) {}

    public function request(CivicReport $report, ?User $requester, string $reason): ReportReopenRequest
    {
        return DB::transaction(function () use ($report, $requester, $reason): ReportReopenRequest {
            $report = CivicReport::query()->lockForUpdate()->findOrFail($report->id);
            $this->assertReopenRequestWindow($report);

            $hasPendingRequest = $report->reopenRequests()
                ->where('status', ReportReopenStatus::Pending->value)
                ->lockForUpdate()
                ->exists();

            if ($hasPendingRequest) {
                throw ValidationException::withMessages(['reason' => 'A reopen request is already waiting for staff review.']);
            }

            $reopenRequest = $report->reopenRequests()->create([
                'requester_id' => $requester?->id,
                'request_number' => 'RR-'.Str::upper((string) Str::ulid()),
                'reason' => trim($reason),
                'status' => ReportReopenStatus::Pending,
                'requested_at' => now(),
            ]);

            $this->auditWriter->record('report_reopen.requested', $reopenRequest, [
                'report_id' => $report->id,
                'is_guest_request' => $requester === null,
            ], $requester);

            return $reopenRequest->load('report');
        });
    }

    public function review(
        ReportReopenRequest $reopenRequest,
        User $reviewer,
        ReportReopenStatus $decision,
        ?string $decisionReason,
    ): ReportReopenRequest {
        if (! in_array($decision, [ReportReopenStatus::Approved, ReportReopenStatus::Rejected], true)) {
            throw ValidationException::withMessages(['decision' => 'Choose an approval or rejection decision.']);
        }

        if ($decision === ReportReopenStatus::Rejected && trim((string) $decisionReason) === '') {
            throw ValidationException::withMessages(['decision_reason' => 'A reason is required when declining a reopen request.']);
        }

        $reviewedRequest = DB::transaction(function () use ($reopenRequest, $reviewer, $decision, $decisionReason): ReportReopenRequest {
            $lockedRequest = ReportReopenRequest::query()->lockForUpdate()->findOrFail($reopenRequest->id);
            $report = CivicReport::query()->lockForUpdate()->findOrFail($lockedRequest->civic_report_id);
            $this->authorizeReview($report, $reviewer);

            if ($lockedRequest->status !== ReportReopenStatus::Pending) {
                throw ValidationException::withMessages(['decision' => 'This reopen request has already been reviewed.']);
            }

            if ($decision === ReportReopenStatus::Approved && $report->status !== CivicReportStatus::Resolved) {
                throw ValidationException::withMessages(['decision' => 'Only a resolved report can be reopened.']);
            }

            $lockedRequest->forceFill([
                'status' => $decision,
                'reviewed_by' => $reviewer->id,
                'decision_reason' => $decisionReason === null ? null : trim($decisionReason),
                'reviewed_at' => now(),
            ])->save();

            if ($decision === ReportReopenStatus::Approved) {
                $previousStatus = $report->status;
                $report->forceFill([
                    'status' => CivicReportStatus::Reopened,
                    'resolved_at' => null,
                    'closed_at' => null,
                    'reopened_at' => now(),
                ])->save();

                ReportStatusEvent::query()->create([
                    'civic_report_id' => $report->id,
                    'actor_id' => $reviewer->id,
                    'event_type' => 'reopen_approved',
                    'from_status' => $previousStatus->value,
                    'to_status' => CivicReportStatus::Reopened->value,
                    'reason' => trim((string) ($decisionReason ?: $lockedRequest->reason)),
                    'citizen_visible' => true,
                    'properties' => ['reopen_request_number' => $lockedRequest->request_number],
                    'occurred_at' => now(),
                ]);
                $this->reportSlaService->restartForReopenedReport($report);
                $this->auditWriter->record('report_reopen.approved', $report, [
                    'reopen_request_id' => $lockedRequest->id,
                    'sla_cycle_number' => $report->slaInstances()->max('cycle_number'),
                ], $reviewer);
                CivicReportStatusChanged::dispatch($report->fresh(['category']));
            }

            $this->auditWriter->record('report_reopen.reviewed', $lockedRequest, [
                'decision' => $decision->value,
                'report_id' => $report->id,
            ], $reviewer);

            return $lockedRequest->fresh(['report', 'reviewer', 'requester']);
        });

        return $reviewedRequest;
    }

    private function assertReopenRequestWindow(CivicReport $report): void
    {
        $windowDays = max(0, (int) config('civictrack.feedback.reopen_window_days', 30));

        if ($report->status !== CivicReportStatus::Resolved
            || $report->resolved_at === null
            || now()->greaterThan($report->resolved_at->copy()->addDays($windowDays))) {
            throw ValidationException::withMessages(['reason' => 'This report is not eligible for a reopen request.']);
        }
    }

    private function authorizeReview(CivicReport $report, User $reviewer): void
    {
        if ($reviewer->can(PermissionName::ReportsManageAll->value)) {
            return;
        }

        if (! $reviewer->can(PermissionName::ReportsTriageDepartment->value)
            || $report->routed_unit_id === null
            || ! UnitMembership::query()
                ->where('user_id', $reviewer->id)
                ->where('organization_unit_id', $report->routed_unit_id)
                ->where('membership_type', RoleName::Staff->value)
                ->where('status', 'active')
                ->whereNull('ended_at')
                ->exists()) {
            throw ValidationException::withMessages(['decision' => 'You cannot review reopen requests for this service unit.']);
        }
    }
}
