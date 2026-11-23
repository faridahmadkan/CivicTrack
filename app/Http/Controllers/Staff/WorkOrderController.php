<?php

namespace App\Http\Controllers\Staff;

use App\Enums\RoleName;
use App\Enums\WorkforceMembershipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateWorkOrderRequest;
use App\Models\CivicReport;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function create(CivicReport $report): View
    {
        $this->authorize('create', [WorkOrder::class, $report]);
        abort_unless($report->status->value === 'acknowledged', 404);

        return view('staff.work-orders.create', ['report' => $report->load(['category', 'routedUnit'])]);
    }

    public function store(
        CreateWorkOrderRequest $request,
        CivicReport $report,
        WorkOrderService $service,
    ): RedirectResponse {
        $workOrder = $service->createForReport($report, $request->user(), $request->validated());

        return to_route('staff.work-orders.show', $workOrder)
            ->with('status', 'Work order and '.count($workOrder->tasks).' tasks created.');
    }

    public function show(Request $request, WorkOrder $workOrder): View
    {
        $this->authorize('view', $workOrder);
        $workOrder->load([
            'report.category', 'organizationUnit', 'tasks.activeAssignment.worker', 'tasks.dependencies',
            'tasks.assignments.worker', 'tasks.assignments.workerRating.reviewer', 'tasks.updates.actor',
            'tasks.verifications.reviewer', 'tasks.evidence',
        ]);

        $eligibleWorkers = User::query()
            ->role(RoleName::Worker->value)
            ->where('account_status', 'active')
            ->whereHas('workerProfile')
            ->whereHas('unitMemberships', static fn (Builder $query) => $query
                ->where('organization_unit_id', $workOrder->organization_unit_id)
                ->where('membership_type', RoleName::Worker->value)
                ->where('status', WorkforceMembershipStatus::Active->value))
            ->orderBy('name')
            ->get();

        return view('staff.work-orders.show', [
            'workOrder' => $workOrder,
            'eligibleWorkers' => $eligibleWorkers,
            'isAdministrator' => $request->user()->can('platform.access'),
        ]);
    }
}
