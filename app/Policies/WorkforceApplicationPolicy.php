<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Models\User;
use App\Models\WorkforceApplication;

class WorkforceApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::StaffApplicationsReview->value)
            || $user->can(PermissionName::WorkerApplicationsReview->value);
    }

    public function view(User $user, WorkforceApplication $workforceApplication): bool
    {
        return $workforceApplication->isOwnedBy($user) || $this->canReviewType($user, $workforceApplication);
    }

    public function create(User $user): bool
    {
        return $user->isActive()
            && $user->hasRole(RoleName::Citizen->value)
            && ! $user->hasAnyRole([RoleName::Staff->value, RoleName::Worker->value]);
    }

    public function update(User $user, WorkforceApplication $workforceApplication): bool
    {
        return $workforceApplication->isOwnedBy($user)
            && $workforceApplication->status->value === 'information_requested';
    }

    public function review(User $user, WorkforceApplication $workforceApplication): bool
    {
        return $this->canReviewType($user, $workforceApplication);
    }

    private function canReviewType(User $user, WorkforceApplication $application): bool
    {
        return $user->can($application->application_type->reviewPermission()->value);
    }
}
