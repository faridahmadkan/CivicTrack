<?php

namespace App\Enums;

enum TaskAssignmentStatus: string
{
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case NeedsRework = 'needs_rework';
    case Completed = 'completed';
    case Declined = 'declined';
    case Reassigned = 'reassigned';
}
