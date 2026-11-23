<?php

namespace App\Services;

use App\Enums\CivicReportStatus;
use App\Models\CivicReport;
use App\Models\ReportRelationship;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportDuplicateService
{
    public function __construct(
        private readonly CivicReportService $civicReportService,
        private readonly AuditWriter $auditWriter,
    ) {}

    public function linkRelated(CivicReport $report, CivicReport $relatedReport, User $actor, string $reason): ReportRelationship
    {
        if ($report->is($relatedReport)) {
            throw ValidationException::withMessages(['related_report' => 'A report cannot be linked to itself.']);
        }

        return DB::transaction(function () use ($report, $relatedReport, $actor, $reason): ReportRelationship {
            $reports = CivicReport::query()
                ->whereKey([$report->id, $relatedReport->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $report = $reports->get($report->id);
            $relatedReport = $reports->get($relatedReport->id);

            if ($report === null || $relatedReport === null) {
                throw ValidationException::withMessages(['related_report' => 'Choose an existing report.']);
            }

            Gate::forUser($actor)->authorize('triage', $report);
            Gate::forUser($actor)->authorize('triage', $relatedReport);

            $firstId = min($report->id, $relatedReport->id);
            $secondId = max($report->id, $relatedReport->id);
            $alreadyLinked = ReportRelationship::query()
                ->where('report_id', $firstId)
                ->where('related_report_id', $secondId)
                ->where('relationship_type', 'related')
                ->exists();

            if ($alreadyLinked) {
                throw ValidationException::withMessages(['related_report' => 'These reports are already linked.']);
            }

            $relationship = ReportRelationship::query()->create([
                'report_id' => $firstId,
                'related_report_id' => $secondId,
                'relationship_type' => 'related',
                'reason' => trim($reason),
                'created_by' => $actor->id,
                'properties' => [],
            ]);

            $this->auditWriter->record('civic_report.related_linked', $report, [
                'related_report_id' => $relatedReport->id,
                'reason' => trim($reason),
            ], $actor);

            return $relationship;
        });
    }

    public function markDuplicate(CivicReport $report, CivicReport $canonicalReport, User $actor, string $reason): CivicReport
    {
        return $this->civicReportService->resolveAsDuplicate($report, $canonicalReport, $actor, $reason, false);
    }

    public function mergeInto(CivicReport $report, CivicReport $canonicalReport, User $actor, string $reason): CivicReport
    {
        return $this->civicReportService->resolveAsDuplicate($report, $canonicalReport, $actor, $reason, true);
    }

    /** @return array<int, array{relationship: ReportRelationship, report: CivicReport, label: string}> */
    public function visibleRelationshipsFor(CivicReport $report, User $actor): array
    {
        return ReportRelationship::query()
            ->where(static function ($query) use ($report): void {
                $query->where('report_id', $report->id)->orWhere('related_report_id', $report->id);
            })
            ->with(['report', 'relatedReport', 'creator'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(function (ReportRelationship $relationship) use ($report, $actor): ?array {
                $isSource = $relationship->report_id === $report->id;
                $otherReport = $isSource ? $relationship->relatedReport : $relationship->report;

                if ($otherReport === null || Gate::forUser($actor)->denies('view', $otherReport)) {
                    return null;
                }

                $label = match ($relationship->relationship_type) {
                    'duplicate' => $isSource ? 'Duplicate of' : 'Duplicate report',
                    'merged_into' => $isSource ? 'Merged into' : 'Merged report',
                    default => 'Related report',
                };

                return ['relationship' => $relationship, 'report' => $otherReport, 'label' => $label];
            })
            ->filter()
            ->values()
            ->all();
    }

    /** @return Collection<int, array{report: CivicReport, match_reason: string}> */
    public function suggestionsFor(CivicReport $report, User $actor): Collection
    {
        $relationships = ReportRelationship::query()
            ->where(static function ($query) use ($report): void {
                $query->where('report_id', $report->id)->orWhere('related_report_id', $report->id);
            })
            ->get(['report_id', 'related_report_id']);
        $linkedIds = $relationships
            ->map(static fn (ReportRelationship $relationship): int => $relationship->report_id === $report->id
                ? $relationship->related_report_id
                : $relationship->report_id)
            ->all();

        /** @var EloquentCollection<int, CivicReport> $candidates */
        $candidates = CivicReport::query()
            ->whereKeyNot($report->id)
            ->where('service_category_id', $report->service_category_id)
            ->whereNotIn('status', [CivicReportStatus::Rejected->value, CivicReportStatus::Duplicate->value, CivicReportStatus::Closed->value])
            ->where('submitted_at', '>=', now()->subDays(180))
            ->when($linkedIds !== [], static fn ($query) => $query->whereNotIn('id', $linkedIds))
            ->with(['location', 'category'])
            ->orderByDesc('submitted_at')
            ->limit(75)
            ->get();

        return $candidates
            ->filter(fn (CivicReport $candidate): bool => Gate::forUser($actor)->allows('triage', $candidate))
            ->map(function (CivicReport $candidate) use ($report): ?array {
                $similarity = $this->titleSimilarity($report->title, $candidate->title);
                $distanceMeters = $this->distanceInMeters($report, $candidate);

                if ($similarity < 0.25 && ($distanceMeters === null || $distanceMeters > 500)) {
                    return null;
                }

                $reason = $distanceMeters !== null && $distanceMeters <= 500
                    ? 'Same category and within 500 m'
                    : 'Same category and similar title';

                return ['report' => $candidate, 'match_reason' => $reason];
            })
            ->filter()
            ->take(8)
            ->values();
    }

    private function titleSimilarity(string $leftTitle, string $rightTitle): float
    {
        $leftWords = $this->titleWords($leftTitle);
        $rightWords = $this->titleWords($rightTitle);

        if ($leftWords === [] || $rightWords === []) {
            return 0;
        }

        $intersection = count(array_intersect($leftWords, $rightWords));

        return $intersection / max(count($leftWords), count($rightWords));
    }

    /** @return array<int, string> */
    private function titleWords(string $title): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', Str::lower($title), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter($words, static fn (string $word): bool => mb_strlen($word) > 2)));
    }

    private function distanceInMeters(CivicReport $leftReport, CivicReport $rightReport): ?float
    {
        $leftLocation = $leftReport->location;
        $rightLocation = $rightReport->location;

        if ($leftLocation?->latitude === null || $leftLocation->longitude === null
            || $rightLocation?->latitude === null || $rightLocation->longitude === null) {
            return null;
        }

        $latitudeDelta = deg2rad((float) $rightLocation->latitude - (float) $leftLocation->latitude);
        $longitudeDelta = deg2rad((float) $rightLocation->longitude - (float) $leftLocation->longitude);
        $leftLatitude = deg2rad((float) $leftLocation->latitude);
        $rightLatitude = deg2rad((float) $rightLocation->latitude);

        return 2 * 6371000 * asin(sqrt(
            sin($latitudeDelta / 2) ** 2
            + cos($leftLatitude) * cos($rightLatitude) * sin($longitudeDelta / 2) ** 2,
        ));
    }
}
