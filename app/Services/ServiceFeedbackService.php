<?php

namespace App\Services;

use App\Enums\CivicReportStatus;
use App\Models\CivicReport;
use App\Models\ServiceFeedback;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceFeedbackService
{
    public function __construct(private readonly AuditWriter $auditWriter) {}

    public function submit(CivicReport $report, ?User $citizen, int $rating, ?string $comment): ServiceFeedback
    {
        return DB::transaction(function () use ($report, $citizen, $rating, $comment): ServiceFeedback {
            $report = CivicReport::query()->lockForUpdate()->findOrFail($report->id);

            if ($citizen !== null && ! $report->isOwnedBy($citizen)) {
                throw ValidationException::withMessages(['feedback' => 'Only the citizen who submitted this report may rate its service.']);
            }

            if ($report->status !== CivicReportStatus::Resolved || $report->resolved_at === null) {
                throw ValidationException::withMessages(['feedback' => 'Service feedback is available after a report has been resolved.']);
            }

            if ($report->serviceFeedback()->exists()) {
                throw ValidationException::withMessages(['feedback' => 'Feedback has already been submitted for this report.']);
            }

            $feedback = $report->serviceFeedback()->create([
                'user_id' => $citizen?->id,
                'rating' => $rating,
                'comment' => $comment === null ? null : trim($comment),
                'submitted_at' => now(),
            ]);

            $this->auditWriter->record('service_feedback.submitted', $report, [
                'rating' => $rating,
                'comment_provided' => $comment !== null && trim($comment) !== '',
                'is_guest_feedback' => $citizen === null,
            ], $citizen);

            return $feedback;
        });
    }
}
