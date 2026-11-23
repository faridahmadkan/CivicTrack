<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CivicReportStatus;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationStatus;
use App\Enums\WorkforceApplicationType;
use App\Http\Controllers\Controller;
use App\Models\CivicReport;
use App\Models\OrganizationUnit;
use App\Models\RegistrationControl;
use App\Models\User;
use App\Models\WorkforceApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminHomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $reviewableTypes = array_values(array_filter(
            WorkforceApplicationType::cases(),
            static fn (WorkforceApplicationType $type): bool => $request->user()->can($type->reviewPermission()->value),
        ));
        $openStatuses = [
            CivicReportStatus::Submitted->value,
            CivicReportStatus::UnderReview->value,
            CivicReportStatus::NeedsInformation->value,
            CivicReportStatus::Acknowledged->value,
            CivicReportStatus::InProgress->value,
            CivicReportStatus::Reopened->value,
        ];

        return view('admin.home', [
            'activeOrganizationUnits' => $request->user()->can('organization.manage')
                ? OrganizationUnit::query()->where('is_active', true)->count()
                : 0,
            'citizenAccounts' => $request->user()->can('accounts.manage')
                ? User::role(RoleName::Citizen->value)->count()
                : 0,
            'pendingApplications' => WorkforceApplication::query()
                ->whereIn('application_type', array_map(static fn (WorkforceApplicationType $type): string => $type->value, $reviewableTypes))
                ->whereIn('status', [
                    WorkforceApplicationStatus::Submitted->value,
                    WorkforceApplicationStatus::UnderReview->value,
                    WorkforceApplicationStatus::InformationRequested->value,
                ])
                ->count(),
            'openRegistrationControls' => $request->user()->can('registrations.manage')
                ? RegistrationControl::query()->where('enabled', true)->count()
                : 0,
            'staffAccounts' => $request->user()->can('accounts.manage')
                ? User::role(RoleName::Staff->value)->where('account_status', 'active')->count()
                : 0,
            'workerAccounts' => $request->user()->can('accounts.manage')
                ? User::role(RoleName::Worker->value)->where('account_status', 'active')->count()
                : 0,
            'openReports' => $request->user()->can('viewAny', CivicReport::class)
                ? CivicReport::query()->whereIn('status', $openStatuses)->count()
                : 0,
            'pendingReopenRequests' => $request->user()->can('reports.view.department')
                ? CivicReport::query()->whereHas('reopenRequests', static fn (Builder $requests) => $requests
                    ->where('status', 'pending'))->count()
                : 0,
            'overdueSlaReports' => $request->user()->can('viewAny', CivicReport::class)
                ? CivicReport::query()->whereIn('status', $openStatuses)
                    ->whereHas('slaInstance', static function (Builder $sla): void {
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
                    })->count()
                : 0,
        ]);
    }
}
