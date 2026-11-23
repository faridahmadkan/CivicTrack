<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\OrganizationUnit;
use App\Models\User;

class OrganizationUnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::OrganizationManage->value);
    }

    public function view(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $user->can(PermissionName::OrganizationManage->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::OrganizationManage->value);
    }

    public function manage(User $user, ?OrganizationUnit $organizationUnit = null): bool
    {
        return $user->can(PermissionName::OrganizationManage->value);
    }

    public function update(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $user->can(PermissionName::OrganizationManage->value);
    }

    public function delete(User $user, OrganizationUnit $organizationUnit): bool
    {
        return false;
    }
}
