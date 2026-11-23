<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Enums\WorkforceApplicationStatus;
use App\Enums\CivicReportStatus;
use App\Http\Controllers\Controller;
use App\Models\OrganizationUnit;
use App\Models\CivicReport;
use App\Models\ReportReopenRequest;
use App\Models\ReportSlaInstance;
use App\Models\ServiceFeedback;
use App\Models\User;
use App\Models\WorkforceApplication;
use Illuminate\Contracts\View\View;

class AdminHomeController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.home', [
            'activeOrganizationUnits' => OrganizationUnit::query()->where('is_active', true)->count(),
            'citizenAccounts' => User::role(RoleName::Citizen->value)->count(),
            'pendingApplications' => WorkforceApplication::query()
                ->whereIn('status', [
                    WorkforceApplicationStatus::Submitted->value,
                    WorkforceApplicationStatus::UnderReview->value,
                    WorkforceApplicationStatus::InformationRequested->value,
                ])
                ->count(),
            'openRegistrationControls' => \App\Models\RegistrationControl::query()->where('enabled', true)->count(),
            'staffAccounts' => User::role(RoleName::Staff->value)->where('account_status', 'active')->count(),
            'workerAccounts' => User::role(RoleName::Worker->value)->where('account_status', 'active')->count(),
            'openReports' => CivicReport::query()->whereIn('status', [
                CivicReportStatus::Submitted->value,
                CivicReportStatus::UnderReview->value,
                CivicReportStatus::NeedsInformation->value,
                CivicReportStatus::Acknowledged->value,
                CivicReportStatus::InProgress->value,
                CivicReportStatus::Reopened->value,
            ])->count(),
            'pendingReopenRequests' => ReportReopenRequest::query()->where('status', 'pending')->count(),
            'overdueSlaReports' => ReportSlaInstance::query()
                ->whereIn('state', ['active', 'breached'])
                ->whereNull('resolved_at')
                ->where(function ($query): void {
                    $query->whereNotNull('first_response_breached_at')
                        ->orWhereNotNull('resolution_breached_at')
                        ->orWhere(function ($target): void {
                        $target->whereNull('first_response_at')->where('first_response_due_at', '<', now());
                    })->orWhere('resolution_due_at', '<', now());
                })
                ->distinct('civic_report_id')
                ->count('civic_report_id'),
            'averageServiceRating' => ServiceFeedback::query()->avg('rating'),
        ]);
    }
}
