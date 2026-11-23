<?php

namespace App\Services;

use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\RoutingDecision;
use App\Models\RoutingRule;
use App\Models\ServiceCategory;

class ReportRoutingEngine
{
    public function route(CivicReport $report, ServiceCategory $category): ?OrganizationUnit
    {
        $now = now();
        $rule = RoutingRule::query()
            ->with('organizationUnit')
            ->where('service_category_id', $category->id)
            ->where('is_active', true)
            ->where(function ($query) use ($now): void {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            })
            ->whereHas('organizationUnit', static fn ($query) => $query->where('is_active', true))
            ->orderBy('priority')
            ->orderBy('id')
            ->first();

        if ($rule !== null) {
            $unit = $rule->organizationUnit;
            $source = 'routing_rule';
            $reason = 'Selected the highest-priority active routing rule for this service category.';
        } else {
            $unit = $category->defaultUnit()->where('is_active', true)->first();
            $rule = null;
            $source = $unit === null ? 'unassigned_queue' : 'category_default';
            $reason = $unit === null
                ? 'No active routing destination was configured; report placed in the unassigned triage queue.'
                : 'No active routing rule matched; report sent to the category default unit.';
        }

        $report->forceFill(['routed_unit_id' => $unit?->id])->save();
        RoutingDecision::query()->create([
            'civic_report_id' => $report->id,
            'routing_rule_id' => $rule?->id,
            'organization_unit_id' => $unit?->id,
            'source' => $source,
            'reason' => $reason,
            'decided_at' => $now,
        ]);

        return $unit;
    }
}
