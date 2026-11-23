<?php

namespace App\Http\Controllers\Citizen;

use App\Enums\CivicReportStatus;
use App\Enums\ReportAttachmentVisibility;
use App\Enums\ReportCommentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCivicReportRequest;
use App\Http\Requests\StoreReportCommentRequest;
use App\Models\CivicReport;
use App\Models\ServiceCategory;
use App\Services\CivicReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CivicReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(CivicReportStatus::class)],
            'scope' => ['nullable', Rule::in(['open', 'feedback_due'])],
        ]);
        $query = CivicReport::query()
            ->where('user_id', $request->user()->id)
            ->with(['category', 'location', 'slaInstance']);

        if (filled($filters['q'] ?? null)) {
            $search = '%'.trim($filters['q']).'%';
            $query->where(static function (Builder $reports) use ($search): void {
                $reports->where('title', 'like', $search)
                    ->orWhere('public_reference', 'like', $search)
                    ->orWhereHas('category', static fn (Builder $categories) => $categories->where('name', 'like', $search));
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        if (($filters['scope'] ?? null) === 'open') {
            $query->whereIn('status', [
                CivicReportStatus::Submitted->value,
                CivicReportStatus::UnderReview->value,
                CivicReportStatus::NeedsInformation->value,
                CivicReportStatus::Acknowledged->value,
                CivicReportStatus::InProgress->value,
                CivicReportStatus::Reopened->value,
            ]);
        } elseif (($filters['scope'] ?? null) === 'feedback_due') {
            $query->where('status', CivicReportStatus::Resolved->value)
                ->whereDoesntHave('serviceFeedback');
        }

        return view('citizen.reports.index', [
            'reports' => $query
                ->orderByDesc('submitted_at')
                ->paginate(10)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('citizen.reports.create', [
            'categories' => ServiceCategory::query()
                ->where('is_active', true)
                ->where('is_public', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'mapCenter' => [config('civictrack.map.latitude'), config('civictrack.map.longitude')],
        ]);
    }

    public function store(StoreCivicReportRequest $request, CivicReportService $service): RedirectResponse
    {
        $attributes = $request->validated();
        $documents = $attributes['documents'] ?? [];
        unset($attributes['documents']);

        $report = $service->submit($request->user(), $attributes, $documents)['report'];

        return to_route('citizen.reports.show', $report)
            ->with('status', 'Your report was submitted. Its reference is '.$report->public_reference.'.');
    }

    public function show(Request $request, CivicReport $report): View
    {
        abort_unless($report->isOwnedBy($request->user()), 404);
        $report->load([
            'category',
            'location',
            'statusEvents' => static fn ($query) => $query->where('citizen_visible', true),
            'attachments' => static fn ($query) => $query->where('visibility', ReportAttachmentVisibility::Reporter->value),
            'comments' => static fn ($query) => $query->where('visibility', ReportCommentVisibility::Public->value),
            'slaInstance',
            'serviceFeedback',
            'reopenRequests.reviewer',
        ]);
        $report->loadCount('workOrders');

        return view('citizen.reports.show', ['report' => $report]);
    }

    public function comment(
        StoreReportCommentRequest $request,
        CivicReport $report,
        CivicReportService $service,
    ): RedirectResponse {
        abort_unless($report->isOwnedBy($request->user()), 404);
        $service->addComment($report, $request->user(), $request->validated('body'), ReportCommentVisibility::Public);

        return to_route('citizen.reports.show', $report)->with('status', 'Your update has been added to the report.');
    }
}
