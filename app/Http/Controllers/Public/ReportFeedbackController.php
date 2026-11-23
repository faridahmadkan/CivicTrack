<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelCivicReportRequest;
use App\Http\Requests\RequestReportDisputeRequest;
use App\Http\Requests\RequestReportReopenRequest;
use App\Http\Requests\SubmitServiceFeedbackRequest;
use App\Models\CivicReport;
use App\Services\CivicReportService;
use App\Services\ReopenReportService;
use App\Services\ReportDisputeService;
use App\Services\ServiceFeedbackService;
use Illuminate\Http\RedirectResponse;

class ReportFeedbackController extends Controller
{
    public function store(
        SubmitServiceFeedbackRequest $request,
        CivicReport $report,
        ServiceFeedbackService $service,
    ): RedirectResponse {
        $service->submit(
            $report,
            $request->user(),
            (int) $request->validated('rating'),
            $request->validated('comment'),
        );

        return to_route($request->user() === null ? 'public.reports.track.show' : 'citizen.reports.show', $report)
            ->with('status', 'Your service feedback has been recorded.');
    }

    public function requestReopen(
        RequestReportReopenRequest $request,
        CivicReport $report,
        ReopenReportService $service,
    ): RedirectResponse {
        $service->request($report, $request->user(), $request->validated('reason'));

        return to_route($request->user() === null ? 'public.reports.track.show' : 'citizen.reports.show', $report)
            ->with('status', 'Your request to reopen this report has been sent for staff review.');
    }

    public function cancel(
        CancelCivicReportRequest $request,
        CivicReport $report,
        CivicReportService $service,
    ): RedirectResponse {
        $service->cancelByReporter($report, $request->user(), $request->validated('reason'));

        return to_route($request->user() === null ? 'public.reports.track.show' : 'citizen.reports.show', $report)
            ->with('status', 'Your report was cancelled.');
    }

    public function dispute(
        RequestReportDisputeRequest $request,
        CivicReport $report,
        ReportDisputeService $service,
    ): RedirectResponse {
        $service->request($report, $request->user(), $request->validated('reason'));

        return to_route($request->user() === null ? 'public.reports.track.show' : 'citizen.reports.show', $report)
            ->with('status', 'Your dispute was sent to the service team for review.');
    }
}
