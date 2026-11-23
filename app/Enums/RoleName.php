<?php

namespace App\Enums;

enum RoleName: string
{
    case Administrator = 'administrator';
    case Staff = 'staff';
    case Worker = 'worker';
    case Citizen = 'citizen';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Staff => 'Staff',
            self::Worker => 'Field worker',
            self::Citizen => 'Citizen',
        };
    }
}
