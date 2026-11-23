<?php

namespace App\Http\Controllers\Citizen;

use App\Enums\ReportAttachmentVisibility;
use App\Enums\ReportCommentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCivicReportRequest;
use App\Http\Requests\StoreReportCommentRequest;
use App\Models\CivicReport;
use App\Models\ServiceCategory;
use App\Services\CivicReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CivicReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('citizen.reports.index', [
            'reports' => CivicReport::query()
                ->where('user_id', $request->user()->id)
                ->with(['category', 'slaInstance'])
                ->orderByDesc('submitted_at')
                ->paginate(10),
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
            'attachments' => static fn ($query) => $query->where('visibility', ReportAttachmentVisibility::Reporter->value),
            'slaInstance',
            'serviceFeedback',
            'reopenRequests.reviewer',
        ]);

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
