<?php

namespace App\Http\Controllers\Staff;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use App\Enums\ReportCommentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\LinkRelatedReportRequest;
use App\Http\Requests\ResolveDuplicateReportRequest;
use App\Http\Requests\ReviewReportDisputeRequest;
use App\Http\Requests\StoreReportCommentRequest;
use App\Http\Requests\TriageCivicReportRequest;
use App\Models\CivicReport;
use App\Models\UnitMembership;
use App\Services\CivicReportService;
use App\Services\ReportDisputeService;
use App\Services\ReportDuplicateService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CivicReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CivicReport::class);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(CivicReportStatus::class)],
        ]);
        $scopeQuery = CivicReport::query();

        if (! $request->user()->can('reports.manage.all')) {
            $unitIds = UnitMembership::query()
                ->where('user_id', $request->user()->id)
                ->where('membership_type', 'staff')
                ->where('status', 'active')
                ->whereNull('ended_at')
                ->pluck('organization_unit_id');

            $scopeQuery->whereIn('routed_unit_id', $unitIds);
        }

        $query = (clone $scopeQuery)
            ->with(['category', 'routedUnit', 'slaInstance'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->orderBy('submitted_at');

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        } else {
            $query->whereIn('status', [
                CivicReportStatus::Submitted->value,
                CivicReportStatus::UnderReview->value,
                CivicReportStatus::Reopened->value,
            ]);
        }

        if (filled($filters['q'] ?? null)) {
            $search = '%'.trim($filters['q']).'%';
            $query->where(static function (Builder $reports) use ($search): void {
                $reports->where('title', 'like', $search)
                    ->orWhere('public_reference', 'like', $search)
                    ->orWhereHas('category', static fn (Builder $categories) => $categories->where('name', 'like', $search));
            });
        }

        return view('staff.reports.index', [
            'reports' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'isAdministrator' => $request->user()->can('platform.access'),
            'triageCount' => (clone $scopeQuery)->whereIn('status', [
                CivicReportStatus::Submitted->value,
                CivicReportStatus::UnderReview->value,
                CivicReportStatus::Reopened->value,
            ])->count(),
            'activeWorkCount' => (clone $scopeQuery)->whereIn('status', [
                CivicReportStatus::Acknowledged->value,
                CivicReportStatus::InProgress->value,
            ])->count(),
            'overdueCount' => (clone $scopeQuery)->whereHas('slaInstance', static function (Builder $sla): void {
                $sla->whereIn('state', ['active', 'breached'])
                    ->whereNull('resolved_at')
                    ->where(function (Builder $targets): void {
                        $targets->whereNotNull('first_response_breached_at')
                            ->orWhereNotNull('resolution_breached_at')
                            ->orWhere(function (Builder $response): void {
                                $response->whereNull('first_response_at')
                                    ->where('first_response_due_at', '<', now());
                            })
                            ->orWhere('resolution_due_at', '<', now());
                    });
            })->count(),
        ]);
    }

    public function show(Request $request, CivicReport $civicReport): View
    {
        $this->authorize('view', $civicReport);
        $civicReport->load([
            'reporter', 'category', 'routedUnit', 'location', 'attachments', 'statusEvents.actor',
            'comments.actor', 'routingDecisions.organizationUnit', 'workOrders', 'slaInstance.events.actor',
            'reopenRequests.requester', 'reopenRequests.reviewer', 'serviceFeedback',
        ]);
        $duplicateService = app(ReportDuplicateService::class);

        return view('staff.reports.show', [
            'report' => $civicReport,
            'isAdministrator' => $request->user()->can('platform.access'),
            'reportRelationships' => $duplicateService->visibleRelationshipsFor($civicReport, $request->user()),
            'duplicateSuggestions' => $request->user()->can('triage', $civicReport)
                ? $duplicateService->suggestionsFor($civicReport, $request->user())
                : collect(),
        ]);
    }

    public function triage(
        TriageCivicReportRequest $request,
        CivicReport $civicReport,
        CivicReportService $service,
    ): RedirectResponse {
        $service->triage(
            $civicReport,
            $request->user(),
            CivicReportStatus::from($request->validated('status')),
            CivicReportPriority::from($request->validated('priority')),
            $request->validated('reason'),
            (bool) $request->validated('is_public', false),
        );

        return to_route('staff.reports.show', $civicReport)->with('status', 'Triage decision recorded.');
    }

    public function comment(
        StoreReportCommentRequest $request,
        CivicReport $civicReport,
        CivicReportService $service,
    ): RedirectResponse {
        $this->authorize('addInternalComment', $civicReport);
        $service->addComment($civicReport, $request->user(), $request->validated('body'), ReportCommentVisibility::Internal);

        return to_route('staff.reports.show', $civicReport)->with('status', 'Internal note added.');
    }

    public function linkRelated(
        LinkRelatedReportRequest $request,
        CivicReport $civicReport,
        CivicReport $relatedReport,
        ReportDuplicateService $service,
    ): RedirectResponse {
        $service->linkRelated($civicReport, $relatedReport, $request->user(), $request->validated('reason'));

        return to_route('staff.reports.show', $civicReport)->with('status', 'Related reports linked.');
    }

    public function markDuplicate(
        ResolveDuplicateReportRequest $request,
        CivicReport $civicReport,
        CivicReport $duplicateReport,
        ReportDuplicateService $service,
    ): RedirectResponse {
        $service->markDuplicate($civicReport, $duplicateReport, $request->user(), $request->validated('reason'));

        return to_route('staff.reports.show', $civicReport)->with('status', 'Report marked as a duplicate.');
    }

    public function mergeDuplicate(
        ResolveDuplicateReportRequest $request,
        CivicReport $civicReport,
        CivicReport $duplicateReport,
        ReportDuplicateService $service,
    ): RedirectResponse {
        $service->mergeInto($civicReport, $duplicateReport, $request->user(), $request->validated('reason'));

        return to_route('staff.reports.show', $civicReport)->with('status', 'Report merged into the selected report.');
    }

    public function reviewDispute(
        ReviewReportDisputeRequest $request,
        CivicReport $civicReport,
        ReportDisputeService $service,
    ): RedirectResponse {
        $service->review(
            $civicReport,
            $request->user(),
            $request->validated('decision'),
            $request->validated('reason'),
        );

        return to_route('staff.reports.show', $civicReport)->with('status', 'Report dispute review recorded.');
    }
}
