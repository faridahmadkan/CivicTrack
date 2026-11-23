<?php

namespace App\Enums;

enum WorkforceMembershipStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Ended = 'ended';
}
