<?php

namespace App\Policies;

use App\Enums\CivicReportStatus;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || $user->can(PermissionName::WorkOrdersManageDepartment->value);
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || ($user->can(PermissionName::WorkOrdersManageDepartment->value)
                && $this->isStaffMemberOfUnit($user, $workOrder->organization_unit_id));
    }

    public function create(User $user, CivicReport $report): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || ($user->can(PermissionName::WorkOrdersManageDepartment->value)
                && $report->status === CivicReportStatus::Acknowledged
                && $report->routed_unit_id !== null
                && $this->isStaffMemberOfUnit($user, $report->routed_unit_id));
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $this->view($user, $workOrder);
    }

    private function isStaffMemberOfUnit(User $user, int $organizationUnitId): bool
    {
        return UnitMembership::query()
            ->where('user_id', $user->id)
            ->where('organization_unit_id', $organizationUnitId)
            ->where('membership_type', RoleName::Staff->value)
            ->where('status', 'active')
            ->exists();
    }
}
