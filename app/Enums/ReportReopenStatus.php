<?php

namespace App\Enums;

enum ReportReopenStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
