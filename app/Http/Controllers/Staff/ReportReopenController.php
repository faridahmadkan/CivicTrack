<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Enums\ReportReopenStatus;
use App\Http\Requests\ReviewReportReopenRequest;
use App\Models\ReportReopenRequest;
use App\Services\ReopenReportService;
use Illuminate\Http\RedirectResponse;

class ReportReopenController extends Controller
{
    public function review(
        ReviewReportReopenRequest $request,
        ReportReopenRequest $reopenRequest,
        ReopenReportService $service,
    ): RedirectResponse {
        $reviewedRequest = $service->review(
            $reopenRequest,
            $request->user(),
            ReportReopenStatus::from($request->validated('decision')),
            $request->validated('decision_reason'),
        );

        return to_route('staff.reports.show', $reviewedRequest->report)
            ->with('status', 'Reopen request decision recorded.');
    }
}
