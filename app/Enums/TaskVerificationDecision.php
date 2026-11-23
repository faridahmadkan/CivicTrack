<?php

namespace App\Enums;

enum TaskVerificationDecision: string
{
    case Approved = 'approved';
    case NeedsRework = 'needs_rework';
}
