<?php

namespace App\Enums;

enum OrganizationUnitType: string
{
    case Department = 'department';
    case Division = 'division';
    case Section = 'section';
    case Team = 'team';
}
