<?php

namespace App\Http\Controllers\Staff;

use App\Enums\CivicReportStatus;
use App\Enums\ReportCommentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportCommentRequest;
use App\Http\Requests\TriageCivicReportRequest;
use App\Models\CivicReport;
use App\Models\ReportAttachment;
use App\Models\UnitMembership;
use App\Services\CivicReportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CivicReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CivicReport::class);
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

        if ($request->filled('status')) {
            abort_unless(CivicReportStatus::tryFrom((string) $request->query('status')) !== null, 404);
            $query->where('status', $request->query('status'));
        } else {
            $query->whereIn('status', [
                CivicReportStatus::Submitted->value,
                CivicReportStatus::UnderReview->value,
                CivicReportStatus::Reopened->value,
            ]);
        }

        return view('staff.reports.index', [
            'reports' => $query->paginate(20)->withQueryString(),
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

        return view('staff.reports.show', [
            'report' => $civicReport,
            'isAdministrator' => $request->user()->can('platform.access'),
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
            \App\Enums\CivicReportPriority::from($request->validated('priority')),
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

    public function downloadAttachment(CivicReport $civicReport, ReportAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $civicReport);
        abort_unless($attachment->civic_report_id === $civicReport->id, 404);
        abort_unless(Storage::disk($attachment->storage_disk)->exists($attachment->storage_path), 404);

        return Storage::disk($attachment->storage_disk)->download(
            $attachment->storage_path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type, 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
