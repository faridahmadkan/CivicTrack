<?php

namespace App\Http\Controllers\Public;

use App\Enums\ReportAttachmentVisibility;
use App\Enums\ReportCommentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccessGuestReportRequest;
use App\Http\Requests\StoreCivicReportRequest;
use App\Http\Requests\StoreReportCommentRequest;
use App\Models\CivicReport;
use App\Models\ServiceCategory;
use App\Services\CivicReportAccessService;
use App\Services\CivicReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportSubmissionController extends Controller
{
    public function create(): View
    {
        return view('public.reports.create', [
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

        $result = $service->submit($request->user(), $attributes, $documents);
        $report = $result['report'];

        if ($request->user() !== null) {
            return to_route('citizen.reports.show', $report)
                ->with('status', 'Your report was submitted. Its reference is '.$report->public_reference.'.');
        }

        return to_route('public.reports.submitted', $report)
            ->with('guest_access_token', $result['guest_access_token'])
            ->with('guest_access_report_id', $report->id);
    }

    public function submitted(Request $request, CivicReport $report): View|RedirectResponse
    {
        if ((int) $request->session()->get('guest_access_report_id') !== $report->id
            || ! is_string($request->session()->get('guest_access_token'))) {
            return to_route('public.reports.access', $report);
        }

        return view('public.reports.submitted', [
            'report' => $report,
            'guestAccessToken' => $request->session()->get('guest_access_token'),
        ]);
    }

    public function access(CivicReport $report): View
    {
        return view('public.reports.access', ['report' => $report]);
    }

    public function verifyAccess(
        AccessGuestReportRequest $request,
        CivicReport $report,
        CivicReportAccessService $accessService,
    ): RedirectResponse {
        if (! $accessService->authenticateGuest($report, $request->validated('token'))) {
            return back()->withErrors(['token' => 'That access code is not valid for this report.']);
        }

        $request->session()->regenerate();
        $request->session()->put('guest_report_access_'.$report->id, true);

        return to_route('public.reports.track.show', $report);
    }

    public function show(Request $request, CivicReport $report): View|RedirectResponse
    {
        $isOwner = $request->user() !== null && $report->isOwnedBy($request->user());
        $hasGuestSession = $request->session()->get('guest_report_access_'.$report->id) === true;

        if (! $isOwner && ! $hasGuestSession) {
            return to_route('public.reports.access', $report);
        }

        $report->load([
            'category', 'location',
            'attachments' => static fn ($query) => $query->where('visibility', ReportAttachmentVisibility::Reporter->value),
            'slaInstance',
            'serviceFeedback', 'reopenRequests.reviewer',
            'statusEvents' => static fn ($query) => $query->where('citizen_visible', true),
            'comments' => static fn ($query) => $query->where('visibility', 'public'),
        ]);
        $report->loadCount('workOrders');

        return view('public.reports.track', ['report' => $report]);
    }

    public function comment(
        StoreReportCommentRequest $request,
        CivicReport $report,
        CivicReportService $service,
    ): RedirectResponse {
        $isOwner = $request->user() !== null && $report->isOwnedBy($request->user());
        $hasGuestSession = $request->session()->get('guest_report_access_'.$report->id) === true;
        abort_unless($isOwner || $hasGuestSession, 404);

        $service->addComment($report, $isOwner ? $request->user() : null, $request->validated('body'), ReportCommentVisibility::Public);

        return to_route('public.reports.track.show', $report)->with('status', 'Your update has been added to the report.');
    }
}
