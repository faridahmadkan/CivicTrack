<?php

namespace App\Enums;

enum WorkTaskStatus: string
{
    case Queued = 'queued';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case SubmittedForReview = 'submitted_for_review';
    case NeedsRework = 'needs_rework';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Queued',
            self::Assigned => 'Assigned',
            self::InProgress => 'In progress',
            self::SubmittedForReview => 'Awaiting verification',
            self::NeedsRework => 'Correction requested',
            self::Completed => 'Verified complete',
            self::Cancelled => 'Cancelled',
        };
    }
}
