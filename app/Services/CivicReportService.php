<?php

namespace App\Services;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use App\Enums\PermissionName;
use App\Enums\ReportAttachmentVisibility;
use App\Enums\ReportCommentVisibility;
use App\Enums\RoleName;
use App\Events\CivicReportStatusChanged;
use App\Models\CivicReport;
use App\Models\ReportComment;
use App\Models\ReportLocation;
use App\Models\ReportRelationship;
use App\Models\ReportStatusEvent;
use App\Models\ServiceCategory;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CivicReportService
{
    public function __construct(
        private readonly ReportRoutingEngine $routingEngine,
        private readonly AuditWriter $auditWriter,
        private readonly ReportSlaService $reportSlaService,
    ) {}

    /**
     * @param  array{service_category_id: int|string, title: string, description: string, latitude: numeric-string|float, longitude: numeric-string|float, address?: string|null, guest_email?: string|null}  $attributes
     * @param  array<int, UploadedFile>  $documents
     * @return array{report: CivicReport, guest_access_token: string|null}
     */
    public function submit(?User $reporter, array $attributes, array $documents): array
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($reporter, $attributes, $documents, &$storedPaths): array {
                $category = ServiceCategory::query()
                    ->whereKey($attributes['service_category_id'])
                    ->where('is_active', true)
                    ->where('is_public', true)
                    ->lockForUpdate()
                    ->first();

                if ($category === null) {
                    throw ValidationException::withMessages(['service_category_id' => 'Choose an available service category.']);
                }

                if ($category->requires_location && (! isset($attributes['latitude'], $attributes['longitude']))) {
                    throw ValidationException::withMessages(['latitude' => 'Choose a location on the map for this service category.']);
                }

                if ($reporter !== null && (! $reporter->isActive() || ! $reporter->hasRole(RoleName::Citizen->value))) {
                    throw ValidationException::withMessages(['report' => 'An active citizen account is required to submit a report.']);
                }

                $report = CivicReport::query()->create([
                    'user_id' => $reporter?->id,
                    'service_category_id' => $category->id,
                    'public_reference' => 'CT-'.Str::upper((string) Str::ulid()),
                    'title' => trim($attributes['title']),
                    'description' => trim($attributes['description']),
                    'status' => CivicReportStatus::Submitted,
                    'priority' => CivicReportPriority::Normal,
                    'guest_contact_email' => $reporter === null ? ($attributes['guest_email'] ?? null) : null,
                    'submitted_at' => now(),
                ]);

                if (isset($attributes['latitude'], $attributes['longitude']) || ! empty($attributes['address'])) {
                    ReportLocation::query()->create([
                        'civic_report_id' => $report->id,
                        'latitude' => $attributes['latitude'] ?? null,
                        'longitude' => $attributes['longitude'] ?? null,
                        'address' => isset($attributes['address']) ? trim($attributes['address']) : null,
                    ]);
                }

                foreach ($documents as $document) {
                    $this->storeAttachment($report, $reporter, $document, $storedPaths);
                }

                $this->routingEngine->route($report, $category);
                $this->reportSlaService->startForReport($report);
                $this->recordStatusEvent($report, $reporter, 'submitted', null, CivicReportStatus::Submitted, null);

                $this->auditWriter->record(
                    'civic_report.submitted',
                    $report,
                    ['service_category_id' => $category->id, 'is_guest_report' => $reporter === null],
                    $reporter,
                );

                $guestToken = null;

                if ($reporter === null) {
                    $guestToken = bin2hex(random_bytes(32));
                    $report->guestAccessTokens()->create([
                        'token_hash' => hash('sha256', $guestToken),
                        'expires_at' => now()->addDays(180),
                    ]);
                }

                return ['report' => $report->fresh(['category', 'location']), 'guest_access_token' => $guestToken];
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('private')->delete($storedPaths);
            }

            throw $exception;
        }
    }

    public function triage(
        CivicReport $report,
        User $actor,
        CivicReportStatus $newStatus,
        CivicReportPriority $newPriority,
        ?string $reason,
        bool $isPublic,
    ): CivicReport {
        $report = DB::transaction(function () use ($report, $actor, $newStatus, $newPriority, $reason, $isPublic): CivicReport {
            $report = CivicReport::query()->lockForUpdate()->findOrFail($report->id);
            $this->authorizeDepartmentAccess($report, $actor);

            if (! in_array($report->status, [
                CivicReportStatus::Submitted,
                CivicReportStatus::UnderReview,
                CivicReportStatus::Reopened,
            ], true)) {
                throw ValidationException::withMessages(['status' => 'This report is no longer available for triage.']);
            }

            if (! in_array($newStatus, [
                CivicReportStatus::UnderReview,
                CivicReportStatus::Acknowledged,
                CivicReportStatus::NeedsInformation,
                CivicReportStatus::Rejected,
            ], true)) {
                throw ValidationException::withMessages(['status' => 'Choose a valid triage decision.']);
            }

            if (in_array($newStatus, [
                CivicReportStatus::NeedsInformation,
                CivicReportStatus::Rejected,
            ], true) && trim((string) $reason) === '') {
                throw ValidationException::withMessages(['reason' => 'A reason is required for this decision.']);
            }

            return $this->applyTriageDecision($report, $actor, $newStatus, $newPriority, $reason, $isPublic);
        });

        $report = $report->fresh(['category', 'location']);
        CivicReportStatusChanged::dispatch($report);

        return $report;
    }

    public function cancelByReporter(CivicReport $report, ?User $reporter, string $reason): CivicReport
    {
        $cancelledReport = DB::transaction(function () use ($report, $reporter, $reason): CivicReport {
            $report = CivicReport::query()->lockForUpdate()->findOrFail($report->id);

            if ($reporter !== null && ! $report->isOwnedBy($reporter)) {
                throw ValidationException::withMessages(['report' => 'Only the report owner can cancel this report.']);
            }

            if (! in_array($report->status, [
                CivicReportStatus::Submitted,
                CivicReportStatus::UnderReview,
                CivicReportStatus::NeedsInformation,
                CivicReportStatus::Acknowledged,
            ], true) || $report->workOrders()->exists()) {
                throw ValidationException::withMessages(['reason' => 'This report can no longer be cancelled by its reporter.']);
            }

            $previousStatus = $report->status;
            $previousPriority = $report->priority;
            $report->forceFill([
                'status' => CivicReportStatus::Cancelled,
                'is_public' => false,
                'closed_at' => now(),
            ])->save();

            $this->reportSlaService->recordFirstResponse($report, $reporter);
            $this->reportSlaService->recordResolved($report, $reporter, CivicReportStatus::Cancelled->value);

            $report->location?->forceFill([
                'public_latitude' => null,
                'public_longitude' => null,
                'privacy_level' => 'precise_private',
            ])->save();

            $this->recordStatusEvent(
                $report,
                $reporter,
                'report_cancelled',
                $previousStatus,
                CivicReportStatus::Cancelled,
                trim($reason),
                $previousPriority,
                $previousPriority,
            );
            $this->auditWriter->record('civic_report.cancelled', $report, ['reason' => trim($reason)], $reporter);

            return $report;
        });

        $cancelledReport = $cancelledReport->fresh(['category', 'location']);
        CivicReportStatusChanged::dispatch($cancelledReport);

        return $cancelledReport;
    }

    public function resolveAsDuplicate(
        CivicReport $report,
        CivicReport $canonicalReport,
        User $actor,
        string $reason,
        bool $merge,
    ): CivicReport {
        if ($report->is($canonicalReport)) {
            throw ValidationException::withMessages(['duplicate_report' => 'A report cannot be linked to itself.']);
        }

        $updatedReport = DB::transaction(function () use ($report, $canonicalReport, $actor, $reason, $merge): CivicReport {
            $lockedReports = CivicReport::query()
                ->whereKey([$report->id, $canonicalReport->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $report = $lockedReports->get($report->id);
            $canonicalReport = $lockedReports->get($canonicalReport->id);

            if ($report === null || $canonicalReport === null) {
                throw ValidationException::withMessages(['duplicate_report' => 'Choose an existing report.']);
            }

            Gate::forUser($actor)->authorize('triage', $report);
            Gate::forUser($actor)->authorize('triage', $canonicalReport);

            if (! in_array($report->status, [
                CivicReportStatus::Submitted,
                CivicReportStatus::UnderReview,
                CivicReportStatus::Reopened,
            ], true)) {
                throw ValidationException::withMessages(['duplicate_report' => 'Only a report awaiting triage can be marked as a duplicate.']);
            }

            if (in_array($canonicalReport->status, [CivicReportStatus::Duplicate, CivicReportStatus::Rejected], true)) {
                throw ValidationException::withMessages(['duplicate_report' => 'Choose an active or resolved report as the canonical report.']);
            }

            if (ReportRelationship::query()
                ->where('report_id', $report->id)
                ->whereIn('relationship_type', ['duplicate', 'merged_into'])
                ->exists()) {
                throw ValidationException::withMessages(['duplicate_report' => 'This report already points to a canonical report.']);
            }

            ReportRelationship::query()->create([
                'report_id' => $report->id,
                'related_report_id' => $canonicalReport->id,
                'relationship_type' => $merge ? 'merged_into' : 'duplicate',
                'reason' => trim($reason),
                'created_by' => $actor->id,
                'properties' => ['canonical_reference' => $canonicalReport->public_reference],
            ]);

            return $this->applyTriageDecision(
                $report,
                $actor,
                CivicReportStatus::Duplicate,
                $report->priority,
                trim($reason),
                false,
                $merge ? 'duplicate_merged' : 'duplicate_marked',
                $merge ? 'civic_report.duplicate_merged' : 'civic_report.duplicate_marked',
                ['related_report_id' => $canonicalReport->id, 'merged' => $merge],
            );
        });

        $updatedReport = $updatedReport->fresh(['category', 'location']);
        CivicReportStatusChanged::dispatch($updatedReport);

        return $updatedReport;
    }

    public function addComment(
        CivicReport $report,
        ?User $actor,
        string $body,
        ReportCommentVisibility $visibility,
    ): ReportComment {
        if ($visibility === ReportCommentVisibility::Internal && ($actor === null || ! $actor->can(PermissionName::ReportsViewDepartment->value))) {
            throw ValidationException::withMessages(['comment' => 'Only authorized staff may add internal notes.']);
        }

        if ($visibility === ReportCommentVisibility::Public && $report->status->isTerminal()) {
            throw ValidationException::withMessages(['comment' => 'This report is closed to further updates.']);
        }

        if ($visibility === ReportCommentVisibility::Internal
            && ! $actor->can(PermissionName::ReportsManageAll->value)
            && ! UnitMembership::query()
                ->where('user_id', $actor->id)
                ->where('organization_unit_id', $report->routed_unit_id)
                ->where('membership_type', 'staff')
                ->where('status', 'active')
                ->exists()) {
            throw ValidationException::withMessages(['comment' => 'You do not have access to add a note to this report.']);
        }

        return DB::transaction(function () use ($report, $actor, $body, $visibility): ReportComment {
            $report = CivicReport::query()->lockForUpdate()->findOrFail($report->id);

            if ($visibility === ReportCommentVisibility::Public && $report->status->isTerminal()) {
                throw ValidationException::withMessages(['comment' => 'This report is closed to further updates.']);
            }

            $comment = $report->comments()->create([
                'actor_id' => $actor?->id,
                'author_label' => $actor === null ? 'Guest reporter' : null,
                'visibility' => $visibility,
                'body' => trim($body),
                'created_at' => now(),
            ]);

            if ($visibility === ReportCommentVisibility::Public && $report->status === CivicReportStatus::NeedsInformation) {
                $previousStatus = $report->status;
                $report->forceFill(['status' => CivicReportStatus::Submitted])->save();
                $this->recordStatusEvent(
                    $report,
                    $actor,
                    'citizen_information_provided',
                    $previousStatus,
                    CivicReportStatus::Submitted,
                    null,
                );
            }

            $this->auditWriter->record(
                'civic_report.comment_added',
                $report,
                ['visibility' => $visibility->value],
                $actor,
            );

            return $comment;
        });
    }

    /** @param array<int, string> $storedPaths */
    private function storeAttachment(CivicReport $report, ?User $reporter, UploadedFile $document, array &$storedPaths): void
    {
        $path = $document->store('civic-reports/'.$report->id, 'private');

        if ($path === false) {
            throw new RuntimeException('The report attachment could not be stored.');
        }

        $storedPaths[] = $path;
        $hash = hash_file('sha256', $document->getRealPath());

        if ($hash === false) {
            throw new RuntimeException('The report attachment could not be verified.');
        }

        $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', basename($document->getClientOriginalName())) ?: 'attachment';
        $report->attachments()->create([
            'uploaded_by' => $reporter?->id,
            'storage_disk' => 'private',
            'storage_path' => $path,
            'original_name' => Str::limit($originalName, 180, ''),
            'mime_type' => $document->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => $document->getSize() ?? 0,
            'sha256' => $hash,
            'is_public' => false,
            'visibility' => ReportAttachmentVisibility::Reporter,
        ]);
    }

    private function authorizeDepartmentAccess(CivicReport $report, User $actor): void
    {
        if ($actor->can(PermissionName::ReportsManageAll->value)) {
            return;
        }

        if (! $actor->can(PermissionName::ReportsTriageDepartment->value)
            || $report->routed_unit_id === null
            || ! UnitMembership::query()
                ->where('user_id', $actor->id)
                ->where('organization_unit_id', $report->routed_unit_id)
                ->where('membership_type', 'staff')
                ->where('status', 'active')
                ->exists()) {
            throw ValidationException::withMessages(['report' => 'You do not have access to triage this service report.']);
        }
    }

    /**
     * @param  array<string, bool|float|int|string|null>  $auditProperties
     */
    private function applyTriageDecision(
        CivicReport $report,
        User $actor,
        CivicReportStatus $newStatus,
        CivicReportPriority $newPriority,
        ?string $reason,
        bool $isPublic,
        string $eventType = 'triage_decision',
        string $auditEvent = 'civic_report.triaged',
        array $auditProperties = [],
    ): CivicReport {
        $location = $report->location;

        if ($isPublic && ($location?->latitude === null || $location?->longitude === null)) {
            throw ValidationException::withMessages([
                'is_public' => 'A report needs a selected map location before it can appear on the public map.',
            ]);
        }

        $previousStatus = $report->status;
        $previousPriority = $report->priority;
        $timestamps = [];

        if ($newStatus === CivicReportStatus::Acknowledged && $report->acknowledged_at === null) {
            $timestamps['acknowledged_at'] = now();
        }

        if (in_array($newStatus, [CivicReportStatus::Rejected, CivicReportStatus::Duplicate, CivicReportStatus::Cancelled], true)) {
            $timestamps['closed_at'] = now();
        }

        $report->forceFill(array_merge([
            'status' => $newStatus,
            'priority' => $newPriority,
            'reviewed_by' => $actor->id,
            'is_public' => $isPublic,
        ], $timestamps))->save();

        $this->reportSlaService->recordFirstResponse($report, $actor);

        if (in_array($newStatus, [CivicReportStatus::Rejected, CivicReportStatus::Duplicate], true)) {
            $this->reportSlaService->recordResolved($report, $actor, $newStatus->value);
        }

        if ($location !== null && $isPublic) {
            $location->forceFill([
                'public_latitude' => round((float) $location->latitude, 2),
                'public_longitude' => round((float) $location->longitude, 2),
                'privacy_level' => 'generalized_public',
            ])->save();
        } elseif ($location !== null) {
            $location->forceFill([
                'public_latitude' => null,
                'public_longitude' => null,
                'privacy_level' => 'precise_private',
            ])->save();
        }

        $this->recordStatusEvent($report, $actor, $eventType, $previousStatus, $newStatus, $reason, $previousPriority, $newPriority);
        $this->auditWriter->record($auditEvent, $report, array_merge([
            'status' => $newStatus->value,
            'priority' => $newPriority->value,
            'is_public' => $isPublic,
        ], $auditProperties), $actor);

        return $report;
    }

    private function recordStatusEvent(
        CivicReport $report,
        ?User $actor,
        string $eventType,
        ?CivicReportStatus $fromStatus,
        CivicReportStatus $toStatus,
        ?string $reason,
        ?CivicReportPriority $fromPriority = null,
        ?CivicReportPriority $toPriority = null,
    ): ReportStatusEvent {
        return ReportStatusEvent::query()->create([
            'civic_report_id' => $report->id,
            'actor_id' => $actor?->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus?->value,
            'to_status' => $toStatus->value,
            'from_priority' => $fromPriority?->value,
            'to_priority' => $toPriority?->value,
            'reason' => $reason === null ? null : trim($reason),
            'citizen_visible' => true,
            'properties' => [],
            'occurred_at' => now(),
        ]);
    }
}
