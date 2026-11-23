<?php

namespace App\Enums;

enum WorkforceApplicationType: string
{
    case Staff = 'staff';
    case Worker = 'worker';

    public function role(): RoleName
    {
        return match ($this) {
            self::Staff => RoleName::Staff,
            self::Worker => RoleName::Worker,
        };
    }

    public function registrationRole(): RoleName
    {
        return $this->role();
    }

    public function reviewPermission(): PermissionName
    {
        return match ($this) {
            self::Staff => PermissionName::StaffApplicationsReview,
            self::Worker => PermissionName::WorkerApplicationsReview,
        };
    }
}
