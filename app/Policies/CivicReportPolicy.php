<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\CivicReport;
use App\Models\User;
use App\Models\UnitMembership;

class CivicReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || $user->can(PermissionName::ReportsViewDepartment->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CivicReport $civicReport): bool
    {
        return $civicReport->isOwnedBy($user)
            || $user->can(PermissionName::ReportsManageAll->value)
            || ($user->can(PermissionName::ReportsViewDepartment->value)
                && $this->isStaffMemberOfUnit($user, $civicReport->routed_unit_id));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->hasRole(RoleName::Citizen->value);
    }

    /** Determine whether the user can triage the report. */
    public function triage(User $user, CivicReport $civicReport): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || ($user->can(PermissionName::ReportsTriageDepartment->value)
                && $this->isStaffMemberOfUnit($user, $civicReport->routed_unit_id));
    }

    /** Determine whether the user can add an internal note. */
    public function addInternalComment(User $user, CivicReport $civicReport): bool
    {
        return $user->can(PermissionName::ReportsManageAll->value)
            || ($user->can(PermissionName::ReportsViewDepartment->value)
                && $this->isStaffMemberOfUnit($user, $civicReport->routed_unit_id));
    }

    private function isStaffMemberOfUnit(User $user, ?int $organizationUnitId): bool
    {
        return $organizationUnitId !== null && UnitMembership::query()
            ->where('user_id', $user->id)
            ->where('organization_unit_id', $organizationUnitId)
            ->where('membership_type', RoleName::Staff->value)
            ->where('status', 'active')
            ->exists();
    }
}
