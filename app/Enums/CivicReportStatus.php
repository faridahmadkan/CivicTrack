<?php

namespace App\Enums;

enum CivicReportStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case NeedsInformation = 'needs_information';
    case Acknowledged = 'acknowledged';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case Rejected = 'rejected';
    case Duplicate = 'duplicate';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::NeedsInformation => 'Information requested',
            self::Acknowledged => 'Acknowledged',
            self::InProgress => 'In progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Reopened => 'Reopened',
            self::Rejected => 'Closed without action',
            self::Duplicate => 'Marked as duplicate',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Closed, self::Rejected, self::Duplicate], true);
    }
}
