<?php

namespace App\Enums;

enum WorkforceApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case InformationRequested = 'information_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::InformationRequested => 'Information requested',
            self::Approved => 'Approved',
            self::Rejected => 'Not approved',
            self::Withdrawn => 'Withdrawn',
        };
    }
}
