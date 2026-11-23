<?php

namespace App\Services;

use App\Enums\ReportSlaState;
use App\Models\CivicReport;
use App\Models\ReportSlaInstance;
use App\Models\ReportSlaPolicy;
use App\Models\SlaEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportSlaService
{
    public function __construct(private readonly AuditWriter $auditWriter) {}

    public function startForReport(CivicReport $report): ReportSlaInstance
    {
        return DB::transaction(function () use ($report): ReportSlaInstance {
            $lockedReport = CivicReport::query()->lockForUpdate()->findOrFail($report->id);
            $existing = ReportSlaInstance::query()
                ->where('civic_report_id', $lockedReport->id)
                ->orderByDesc('cycle_number')
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            return $this->createCycle($lockedReport, 1, $lockedReport->submitted_at ?? now());
        });
    }

    public function restartForReopenedReport(CivicReport $report): ReportSlaInstance
    {
        return DB::transaction(function () use ($report): ReportSlaInstance {
            $lockedReport = CivicReport::query()->lockForUpdate()->findOrFail($report->id);
            $latestCycle = ReportSlaInstance::query()
                ->where('civic_report_id', $lockedReport->id)
                ->orderByDesc('cycle_number')
                ->lockForUpdate()
                ->first();

            if ($latestCycle !== null && $latestCycle->resolved_at === null && $latestCycle->state !== ReportSlaState::NotApplicable) {
                throw new \LogicException('An unresolved SLA cycle cannot be restarted.');
            }

            $cycleNumber = ($latestCycle?->cycle_number ?? 0) + 1;

            return $this->createCycle($lockedReport, $cycleNumber, now());
        });
    }

    private function createCycle(CivicReport $report, int $cycleNumber, Carbon $startedAt): ReportSlaInstance
    {
        $policy = $this->matchingPolicies($report, $startedAt)
            ->sort(function (ReportSlaPolicy $left, ReportSlaPolicy $right): int {
                return $this->specificity($right) <=> $this->specificity($left)
                    ?: $right->version <=> $left->version
                    ?: $right->id <=> $left->id;
            })
            ->first();

        if ($policy === null) {
            $instance = ReportSlaInstance::query()->create([
                'civic_report_id' => $report->id,
                'cycle_number' => $cycleNumber,
                'started_at' => $startedAt,
                'state' => ReportSlaState::NotApplicable,
            ]);
            $this->appendEvent($instance, 'no_matching_policy', null, 'No active service level policy matched this report cycle.');
            $this->auditWriter->record('report_sla.not_applicable', $instance, ['cycle_number' => $cycleNumber]);

            return $instance;
        }

        $firstResponseDueAt = $startedAt->copy()->addMinutes($policy->first_response_minutes);
        $resolutionDueAt = $startedAt->copy()->addMinutes($policy->resolution_minutes);
        $instance = ReportSlaInstance::query()->create([
            'civic_report_id' => $report->id,
            'cycle_number' => $cycleNumber,
            'report_sla_policy_id' => $policy->id,
            'policy_name_snapshot' => $policy->name,
            'policy_version_snapshot' => $policy->version,
            'first_response_target_minutes' => $policy->first_response_minutes,
            'resolution_target_minutes' => $policy->resolution_minutes,
            'escalation_repeat_minutes' => $policy->escalation_repeat_minutes,
            'started_at' => $startedAt,
            'first_response_due_at' => $firstResponseDueAt,
            'resolution_due_at' => $resolutionDueAt,
            'next_escalation_at' => $firstResponseDueAt->lessThanOrEqualTo($resolutionDueAt)
                ? $firstResponseDueAt
                : $resolutionDueAt,
            'state' => ReportSlaState::Active,
        ]);

        $this->appendEvent($instance, 'sla_started', null, 'SLA targets started from the applicable service policy.', [
            'policy_id' => $policy->id,
            'policy_version' => $policy->version,
            'cycle_number' => $cycleNumber,
            'first_response_due_at' => $firstResponseDueAt->toIso8601String(),
            'resolution_due_at' => $resolutionDueAt->toIso8601String(),
        ]);
        $this->auditWriter->record('report_sla.started', $instance, [
            'policy_id' => $policy->id,
            'policy_version' => $policy->version,
            'cycle_number' => $cycleNumber,
        ]);

        return $instance;
    }

    public function recordFirstResponse(CivicReport $report, ?User $actor = null): void
    {
        $instance = ReportSlaInstance::query()
            ->where('civic_report_id', $report->id)
            ->orderByDesc('cycle_number')
            ->lockForUpdate()
            ->first();

        if ($instance === null
            || $instance->state === ReportSlaState::NotApplicable
            || $instance->first_response_due_at === null
            || $instance->first_response_at !== null
            || $instance->resolved_at !== null) {
            return;
        }

        $respondedAt = now();
        $isLate = $respondedAt->greaterThan($instance->first_response_due_at);
        $instance->forceFill([
            'first_response_at' => $respondedAt,
            'first_response_breached_at' => $isLate ? $respondedAt : null,
            'state' => $isLate ? ReportSlaState::Breached : $instance->state,
            'next_escalation_at' => $isLate
                ? $respondedAt
                : ($instance->state === ReportSlaState::Breached
                    ? $instance->next_escalation_at
                    : $instance->resolution_due_at),
        ])->save();

        $this->appendEvent(
            $instance,
            $isLate ? 'first_response_breached' : 'first_response_met',
            $actor,
            $isLate ? 'The first staff response was recorded after its target.' : 'The first staff response met its target.',
            ['responded_at' => $respondedAt->toIso8601String()],
        );
        $this->auditWriter->record(
            $isLate ? 'report_sla.first_response_breached' : 'report_sla.first_response_met',
            $instance,
            [],
            $actor,
        );
    }

    public function recordResolved(CivicReport $report, ?User $actor, string $reason): void
    {
        $instance = ReportSlaInstance::query()
            ->where('civic_report_id', $report->id)
            ->orderByDesc('cycle_number')
            ->lockForUpdate()
            ->first();

        if ($instance === null
            || $instance->state === ReportSlaState::NotApplicable
            || $instance->resolution_due_at === null
            || $instance->resolved_at !== null) {
            return;
        }

        $this->recordFirstResponse($report, $actor);
        $instance->refresh();
        $resolvedAt = now();
        $resolutionLate = $resolvedAt->greaterThan($instance->resolution_due_at);
        $firstResponseLate = $instance->first_response_breached_at !== null;
        $instance->forceFill([
            'resolved_at' => $resolvedAt,
            'resolution_breached_at' => $resolutionLate ? $resolvedAt : null,
            'next_escalation_at' => null,
            'state' => $firstResponseLate || $resolutionLate ? ReportSlaState::Breached : ReportSlaState::Met,
        ])->save();

        $this->appendEvent(
            $instance,
            $resolutionLate ? 'resolution_breached' : 'resolution_met',
            $actor,
            $resolutionLate ? 'The report was closed after its resolution target.' : 'The report was closed within its resolution target.',
            ['resolved_at' => $resolvedAt->toIso8601String(), 'reason' => $reason],
        );
        $this->auditWriter->record(
            $resolutionLate ? 'report_sla.resolution_breached' : 'report_sla.resolution_met',
            $instance,
            ['reason' => $reason],
            $actor,
        );
    }

    /** @return array{instance: ReportSlaInstance, report: CivicReport, targets: array<int, string>}|null */
    public function processDueEscalation(int $instanceId): ?array
    {
        return DB::transaction(function () use ($instanceId): ?array {
            $instance = ReportSlaInstance::query()->lockForUpdate()->find($instanceId);

            if ($instance === null
                || $instance->resolved_at !== null
                || ! in_array($instance->state, [ReportSlaState::Active, ReportSlaState::Breached], true)) {
                return null;
            }

            $now = now();
            $newlyBreachedTargets = [];

            if ($instance->first_response_at === null
                && $instance->first_response_breached_at === null
                && $instance->first_response_due_at->lessThan($now)) {
                $instance->first_response_breached_at = $now;
                $newlyBreachedTargets[] = 'first_response';
                $this->appendEvent($instance, 'first_response_breached', null, 'The first response target expired without a staff response.');
            }

            if ($instance->resolution_breached_at === null && $instance->resolution_due_at->lessThan($now)) {
                $instance->resolution_breached_at = $now;
                $newlyBreachedTargets[] = 'resolution';
                $this->appendEvent($instance, 'resolution_breached', null, 'The resolution target expired while the report remained open.');
            }

            $isFirstEscalation = $newlyBreachedTargets !== [];
            $isRepeatEscalationDue = $instance->next_escalation_at !== null
                && $instance->next_escalation_at->lessThan($now);

            if (! $isFirstEscalation && ! $isRepeatEscalationDue) {
                if ($newlyBreachedTargets !== []) {
                    $instance->forceFill([
                        'state' => ReportSlaState::Breached,
                        'next_escalation_at' => $now->copy()->addMinutes($instance->escalation_repeat_minutes),
                    ])->save();
                    $this->auditWriter->record('report_sla.target_breached', $instance, [
                        'targets' => implode(',', $newlyBreachedTargets),
                    ]);
                }

                return null;
            }

            if ($newlyBreachedTargets === []) {
                if ($instance->first_response_breached_at !== null) {
                    $newlyBreachedTargets[] = 'first_response';
                }

                if ($instance->resolution_breached_at !== null) {
                    $newlyBreachedTargets[] = 'resolution';
                }
            }

            $instance->forceFill([
                'state' => ReportSlaState::Breached,
                'escalation_count' => $instance->escalation_count + 1,
                'next_escalation_at' => $now->copy()->addMinutes($instance->escalation_repeat_minutes),
            ])->save();

            $this->appendEvent($instance, 'escalation_sent', null, 'An SLA escalation was raised for an overdue report.', [
                'escalation_count' => $instance->escalation_count,
                'targets' => $newlyBreachedTargets,
            ]);
            $this->auditWriter->record('report_sla.escalated', $instance, [
                'escalation_count' => $instance->escalation_count,
                'targets' => implode(',', $newlyBreachedTargets),
            ]);

            $report = CivicReport::query()->with('routedUnit')->findOrFail($instance->civic_report_id);

            return [
                'instance' => $instance->fresh(),
                'report' => $report,
                'targets' => $newlyBreachedTargets,
            ];
        });
    }

    /** @return Collection<int, ReportSlaPolicy> */
    private function matchingPolicies(CivicReport $report, Carbon $startedAt): Collection
    {
        return ReportSlaPolicy::query()
            ->where('is_active', true)
            ->where(function ($query) use ($startedAt): void {
                $query->whereNull('effective_at')->orWhere('effective_at', '<=', $startedAt);
            })
            ->where(function ($query) use ($startedAt): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', $startedAt);
            })
            ->where(function ($query) use ($report): void {
                $query->whereNull('service_category_id')->orWhere('service_category_id', $report->service_category_id);
            })
            ->where(function ($query) use ($report): void {
                $query->whereNull('organization_unit_id')->orWhere('organization_unit_id', $report->routed_unit_id);
            })
            ->where(function ($query) use ($report): void {
                $query->whereNull('priority')->orWhere('priority', $report->priority->value);
            })
            ->get();
    }

    private function specificity(ReportSlaPolicy $policy): int
    {
        return ($policy->service_category_id !== null ? 4 : 0)
            + ($policy->organization_unit_id !== null ? 2 : 0)
            + ($policy->priority !== null ? 1 : 0);
    }

    /** @param array<string, bool|float|int|string|array<int, string>|null> $properties */
    private function appendEvent(
        ReportSlaInstance $instance,
        string $eventType,
        ?User $actor,
        string $details,
        array $properties = [],
    ): SlaEvent {
        return $instance->events()->create([
            'actor_id' => $actor?->id,
            'event_type' => $eventType,
            'details' => $details,
            'properties' => $properties,
            'occurred_at' => now(),
        ]);
    }
}
