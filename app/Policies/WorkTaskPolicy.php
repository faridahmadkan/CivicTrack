<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Enums\TaskAssignmentStatus;
use App\Models\TaskAssignment;
use App\Models\UnitMembership;
use App\Models\User;
use App\Models\WorkTask;

class WorkTaskPolicy
{
    public function view(User $user, WorkTask $task): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || ($user->can(PermissionName::WorkOrdersManageDepartment->value)
                && $this->isStaffMemberOfUnit($user, $task->workOrder->organization_unit_id))
            || ($user->can(PermissionName::AssignmentsViewOwn->value) && $this->hasActiveAssignment($user, $task));
    }

    public function assign(User $user, WorkTask $task): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || ($user->can(PermissionName::AssignmentsManageDepartment->value)
                && $this->isStaffMemberOfUnit($user, $task->workOrder->organization_unit_id));
    }

    public function verify(User $user, WorkTask $task): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || ($user->can(PermissionName::VerificationManageDepartment->value)
                && $this->isStaffMemberOfUnit($user, $task->workOrder->organization_unit_id));
    }

    public function updateOwn(User $user, WorkTask $task): bool
    {
        return $user->isActive()
            && $user->hasRole(RoleName::Worker->value)
            && $user->can(PermissionName::AssignmentsUpdateOwn->value)
            && $this->hasActiveAssignment($user, $task);
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

    private function hasActiveAssignment(User $user, WorkTask $task): bool
    {
        return TaskAssignment::query()
            ->where('work_task_id', $task->id)
            ->where('worker_id', $user->id)
            ->whereNull('ended_at')
            ->whereIn('status', [
                TaskAssignmentStatus::Assigned->value,
                TaskAssignmentStatus::Accepted->value,
                TaskAssignmentStatus::InProgress->value,
                TaskAssignmentStatus::Submitted->value,
                TaskAssignmentStatus::NeedsRework->value,
            ])
            ->exists();
    }
}
