<?php

namespace App\Enums;

enum ReportSlaState: string
{
    case Active = 'active';
    case Breached = 'breached';
    case Met = 'met';
    case Closed = 'closed';
    case NotApplicable = 'not_applicable';
}
