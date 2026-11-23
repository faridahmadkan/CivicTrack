<?php

namespace App\Services;

use App\Enums\CivicReportStatus;
use App\Events\CivicReportStatusChanged;
use App\Models\CivicReport;
use App\Models\ReportStatusEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ReportDisputeService
{
    public function __construct(
        private readonly AuditWriter $auditWriter,
        private readonly ReportSlaService $reportSlaService,
    ) {}

    public function request(CivicReport $report, ?User $reporter, string $reason): CivicReport
    {
        $disputedReport = DB::transaction(function () use ($report, $reporter, $reason): CivicReport {
            $report = CivicReport::query()->lockForUpdate()->findOrFail($report->id);

            if ($reporter !== null && ! $report->isOwnedBy($reporter)) {
                throw ValidationException::withMessages(['report' => 'Only the report owner can dispute its outcome.']);
            }

            if (! in_array($report->status, [
                CivicReportStatus::Resolved,
                CivicReportStatus::Closed,
                CivicReportStatus::Rejected,
                CivicReportStatus::Duplicate,
                CivicReportStatus::Cancelled,
            ], true)) {
                throw ValidationException::withMessages(['reason' => 'Only a closed report outcome can be disputed.']);
            }

            $closedAt = $report->resolved_at ?? $report->closed_at;
            $windowDays = max(0, (int) config('civictrack.feedback.reopen_window_days', 30));

            if ($closedAt === null || now()->greaterThan($closedAt->copy()->addDays($windowDays))) {
                throw ValidationException::withMessages(['reason' => 'The report dispute window has expired.']);
            }

            $location = $report->location;
            $properties = [
                'previous_is_public' => $report->is_public,
                'public_latitude' => $location?->public_latitude,
                'public_longitude' => $location?->public_longitude,
                'privacy_level' => $location?->privacy_level,
            ];
            $previousStatus = $report->status;
            $report->forceFill(['status' => CivicReportStatus::Disputed, 'is_public' => false])->save();

            $location?->forceFill([
                'public_latitude' => null,
                'public_longitude' => null,
                'privacy_level' => 'precise_private',
            ])->save();

            ReportStatusEvent::query()->create([
                'civic_report_id' => $report->id,
                'actor_id' => $reporter?->id,
                'event_type' => 'dispute_submitted',
                'from_status' => $previousStatus->value,
                'to_status' => CivicReportStatus::Disputed->value,
                'reason' => trim($reason),
                'citizen_visible' => true,
                'properties' => $properties,
                'occurred_at' => now(),
            ]);

            $this->auditWriter->record('civic_report.disputed', $report, [
                'previous_status' => $previousStatus->value,
                'reason' => trim($reason),
            ], $reporter);

            return $report;
        });

        $disputedReport = $disputedReport->fresh(['category', 'location']);
        CivicReportStatusChanged::dispatch($disputedReport);

        return $disputedReport;
    }

    public function review(CivicReport $report, User $reviewer, string $decision, string $reason): CivicReport
    {
        if (! in_array($decision, ['reopen', 'uphold'], true)) {
            throw ValidationException::withMessages(['decision' => 'Choose whether to reopen or uphold the outcome.']);
        }

        $reviewedReport = DB::transaction(function () use ($report, $reviewer, $decision, $reason): CivicReport {
            $report = CivicReport::query()->lockForUpdate()->findOrFail($report->id);
            Gate::forUser($reviewer)->authorize('triage', $report);

            if ($report->status !== CivicReportStatus::Disputed) {
                throw ValidationException::withMessages(['decision' => 'This report has no pending dispute.']);
            }

            $disputeEvent = $report->statusEvents()
                ->where('event_type', 'dispute_submitted')
                ->orderByDesc('id')
                ->first();
            $previousStatus = $disputeEvent?->from_status;

            if ($disputeEvent === null || ! $previousStatus instanceof CivicReportStatus) {
                throw ValidationException::withMessages(['decision' => 'The original report outcome could not be verified.']);
            }

            $newStatus = $decision === 'reopen' ? CivicReportStatus::Reopened : $previousStatus;
            $reportAttributes = ['status' => $newStatus];

            if ($decision === 'reopen') {
                $reportAttributes['resolved_at'] = null;
                $reportAttributes['closed_at'] = null;
                $reportAttributes['reopened_at'] = now();
                $reportAttributes['is_public'] = false;
            } else {
                $reportAttributes['is_public'] = (bool) ($disputeEvent->properties['previous_is_public'] ?? false);
            }

            $report->forceFill($reportAttributes)->save();

            if ($decision === 'uphold') {
                $report->location?->forceFill([
                    'public_latitude' => $disputeEvent->properties['public_latitude'] ?? null,
                    'public_longitude' => $disputeEvent->properties['public_longitude'] ?? null,
                    'privacy_level' => $disputeEvent->properties['privacy_level'] ?? 'precise_private',
                ])->save();
            }

            ReportStatusEvent::query()->create([
                'civic_report_id' => $report->id,
                'actor_id' => $reviewer->id,
                'event_type' => 'dispute_reviewed',
                'from_status' => CivicReportStatus::Disputed->value,
                'to_status' => $newStatus->value,
                'reason' => trim($reason),
                'citizen_visible' => true,
                'properties' => ['decision' => $decision],
                'occurred_at' => now(),
            ]);

            if ($decision === 'reopen') {
                $this->reportSlaService->restartForReopenedReport($report);
            }

            $this->auditWriter->record('civic_report.dispute_reviewed', $report, [
                'decision' => $decision,
                'previous_status' => $previousStatus->value,
                'status' => $newStatus->value,
            ], $reviewer);

            return $report;
        });

        $reviewedReport = $reviewedReport->fresh(['category', 'location']);
        CivicReportStatusChanged::dispatch($reviewedReport);

        return $reviewedReport;
    }
}
