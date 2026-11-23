<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Enums\CivicReportStatus;
use App\Models\CivicReport;
use App\Models\WorkforceApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $userId = $request->user()->id;
        $reports = CivicReport::query()->where('user_id', $userId);

        return view('citizen.home', [
            'recentReports' => (clone $reports)
                ->with(['category', 'slaInstance'])
                ->orderByDesc('submitted_at')
                ->limit(5)
                ->get(),
            'totalReports' => (clone $reports)->count(),
            'openReports' => (clone $reports)->whereIn('status', [
                CivicReportStatus::Submitted->value,
                CivicReportStatus::UnderReview->value,
                CivicReportStatus::NeedsInformation->value,
                CivicReportStatus::Acknowledged->value,
                CivicReportStatus::InProgress->value,
                CivicReportStatus::Reopened->value,
            ])->count(),
            'resolvedReports' => (clone $reports)->where('status', CivicReportStatus::Resolved->value)->count(),
            'feedbackDue' => (clone $reports)
                ->where('status', CivicReportStatus::Resolved->value)
                ->whereDoesntHave('serviceFeedback')
                ->count(),
            'workforceApplications' => WorkforceApplication::query()->where('user_id', $userId)->count(),
        ]);
    }
}
